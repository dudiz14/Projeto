<?php
session_start();
include("conexao.php");

// Proteção: verifica se o usuário está logado e se o método é POST
if (!isset($_SESSION['id_empresa']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

// Pega os dados do formulário da modal
$id_empresa = $_SESSION['id_empresa'];
$metodos_pagamento = $_POST['metodo'] ?? []; 
$valores_pagos = $_POST['valor'] ?? [];      
$valor_total_venda = (float) ($_POST['valor_total_venda'] ?? 0.00);

// Certifica-se de que o carrinho não está vazio
if (empty($_SESSION['carrinho'])) {
    header("Location: index.php?page=carrinho&erro=carrinho_vazio");
    exit;
}

// --- Validação dos Dados ---
$soma_valores_pagos = 0;
foreach ($valores_pagos as $valor) {
    // **CORREÇÃO APLICADA AQUI**
    // Limpa a string de valor vinda do formulário (ex: "R$ 1.234,56")
    // 1. Remove "R$" e espaços
    $valor_limpo = str_replace(['R$', ' '], '', $valor);
    // 2. Remove o ponto de milhar
    $valor_limpo = str_replace('.', '', $valor_limpo);
    // 3. Troca a vírgula decimal por ponto
    $valor_limpo = str_replace(',', '.', $valor_limpo);
    
    // Converte para float e soma
    $soma_valores_pagos += floatval($valor_limpo);
}

// Verifica se a soma dos pagamentos bate com o total da venda (com uma pequena margem para erros de ponto flutuante)
if (abs($soma_valores_pagos - $valor_total_venda) > 0.01) {
    // Se a validação falhar, redireciona com erro
    header("Location: index.php?page=carrinho&erro=valor_invalido");
    exit;
}

// --- Processamento da Venda com TRANSAÇÃO ---
$conn->begin_transaction();

try {
    // 1. Monta a descrição do pagamento
    $descricao_pagamento = "";
    for ($i = 0; $i < count($metodos_pagamento); $i++) {
        // Apenas para exibição, o valor formatado é usado na descrição
        $valor_formatado = htmlspecialchars($valores_pagos[$i]);
        $descricao_pagamento .= htmlspecialchars($metodos_pagamento[$i]) . ": " . $valor_formatado . "; ";
    }
    $descricao_pagamento = rtrim($descricao_pagamento, '; ');


    // 2. Insere a venda na tabela 'vendas'
    $stmt_venda = $conn->prepare("INSERT INTO vendas (id_empresa, valor_total, forma_pagamento, data_venda) VALUES (?, ?, ?, NOW())");
    $stmt_venda->bind_param("ids", $id_empresa, $valor_total_venda, $descricao_pagamento);
    $stmt_venda->execute();
    
    // Pega o ID da venda recém-inserida
    $id_venda = $conn->insert_id;

    // Busca os preços dos produtos de uma vez para calcular o subtotal
    $product_ids = array_keys($_SESSION['carrinho']);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stmt_precos = $conn->prepare("SELECT id, preco FROM produtos WHERE id IN ($placeholders)");
    $stmt_precos->bind_param(str_repeat('i', count($product_ids)), ...$product_ids);
    $stmt_precos->execute();
    $result_precos = $stmt_precos->get_result();
    $precos_produtos = [];
    while($row = $result_precos->fetch_assoc()){
        $precos_produtos[$row['id']] = $row['preco'];
    }
    
    // 3. Prepara a inserção dos itens na tabela 'itens_venda' (com subtotal)
    $stmt_itens_venda = $conn->prepare("INSERT INTO itens_venda (id_venda, id_produto, quantidade, subtotal) VALUES (?, ?, ?, ?)");
    
    // 4. Prepara o update de estoque (dando baixa)
    $stmt_estoque = $conn->prepare("UPDATE produtos SET quantidade = quantidade - ? WHERE id = ? AND id_empresa = ?");
    
    $carrinho = $_SESSION['carrinho'];

    foreach ($carrinho as $id_produto => $quantidade) {
        $quantidade_int = intval($quantidade);
        if ($quantidade_int <= 0) continue;

        // **MELHORIA: Calcula e insere o subtotal**
        $preco_unitario = $precos_produtos[$id_produto] ?? 0;
        $subtotal = $preco_unitario * $quantidade_int;
        
        // 4.1. Insere o item na tabela 'itens_venda'
        $stmt_itens_venda->bind_param("iiid", $id_venda, $id_produto, $quantidade_int, $subtotal);
        $stmt_itens_venda->execute();

        // 4.2. Dá baixa no estoque
        $stmt_estoque->bind_param("iii", $quantidade_int, $id_produto, $id_empresa);
        $stmt_estoque->execute();
    }

    // 5. Se tudo deu certo até aqui, confirma a transação
    $conn->commit();

    // 6. Limpa o carrinho
    unset($_SESSION['carrinho']); 

    // 7. Redireciona para o relatório de vendas com mensagem de sucesso
    header("Location: index.php?page=relatorio_vendas&sucesso=venda_finalizada");
    exit;

} catch (Exception $e) {
    // 8. Se algo deu errado, desfaz todas as operações no banco
    $conn->rollback();

    // Para depurar, você pode registrar o erro se tiver um sistema de logs
    // error_log("Erro ao finalizar venda: " . $e->getMessage());

    // 9. Redireciona de volta ao carrinho com uma mensagem de erro
    header("Location: index.php?page=carrinho&erro=processamento");
    exit;
}
?>