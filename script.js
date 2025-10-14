// script.js

// Função para adicionar item ao carrinho sem recarregar a página
function addToCart(productId) {
    const formData = new FormData();
    formData.append('id_produto', productId);

    fetch('add_carrinho.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateCartCount(data.total_items);
        }
    })
    .catch(error => console.error('Erro:', error));
}

// Função para atualizar o número no ícone do carrinho
function updateCartCount(count) {
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        // Garante que o contador é um número inteiro
        const newCount = parseInt(count) || 0; 
        cartCountElement.innerText = newCount;
        
        // CORREÇÃO: Esconde/mostra o contador
        cartCountElement.style.display = newCount > 0 ? 'inline-block' : 'none'; 
    }
}

// Quando a página carregar, a lógica a seguir é executada:
document.addEventListener('DOMContentLoaded', () => {
    // O PHP já inseriu o valor correto no HTML (0 após a compra),
    // este bloco apenas garante que a função de esconder/mostrar seja aplicada
    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement) {
        const initialCount = parseInt(cartCountElement.innerText) || 0;
        updateCartCount(initialCount); 
    }
    
    // Se o seu 'get_carrinho_qtd.php' for usado em outras páginas, mantenha o fetch
    fetch('get_carrinho_qtd.php')
        .then(response => response.json())
        .then(data => {
            // Este fetch atualiza o contador caso a contagem inicial do PHP tenha sido 0
            // e por algum motivo o servidor retorne um valor diferente.
            updateCartCount(data.total_items);
        })
        .catch(error => console.error('Erro ao buscar quantidade do carrinho:', error));
});

// --- LÓGICA DA MODAL DE PAGAMENTO (SEM ALTERAÇÕES FUNCIONAIS) ---

const modal = document.getElementById('modalPagamento');
const totalCarrinhoElement = document.getElementById('valorTotalCarrinho');
const modalTotalValorElement = document.getElementById('modalTotalValor');
const valorRestanteElement = document.getElementById('valorRestante');
const pagamentosContainer = document.getElementById('pagamentos-container');
const btnConfirmarPagamento = document.getElementById('btnConfirmarPagamento');

let totalVenda = 0;

// Abre a modal
function abrirModalPagamento() {
    if (!totalCarrinhoElement) return;

    const cartCountElement = document.getElementById('cart-count');
    if (cartCountElement && parseInt(cartCountElement.innerText) === 0) {
        alert("O carrinho está vazio!");
        return;
    }
    
    totalVenda = parseFloat(totalCarrinhoElement.getAttribute('data-total'));
    modalTotalValorElement.innerText = totalVenda.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    
    pagamentosContainer.innerHTML = '';
    adicionarPagamento();

    atualizarValorRestante();
    modal.style.display = 'flex';
}

// Fecha a modal
function fecharModalPagamento() {
    modal.style.display = 'none';
}

// Cria uma nova linha para adicionar uma forma de pagamento
function adicionarPagamento() {
    const div = document.createElement('div');
    div.classList.add('pagamento-row');
    div.innerHTML = `
        <select name="metodo[]" required>
            <option value="Dinheiro">Dinheiro</option>
            <option value="Cartão de Crédito">Cartão de Crédito</option>
            <option value="Cartão de Débito">Cartão de Débito</option>
            <option value="PIX">PIX</option>
        </select>
        <input type="text" name="valor[]" class="valor-pago" placeholder="R$ 0,00" onkeyup="atualizarValorRestante()" required>
        <button type="button" class="remove-payment-btn" onclick="removerPagamento(this)">&times;</button>
    `;
    pagamentosContainer.appendChild(div);

    const inputValor = div.querySelector('.valor-pago');
    inputValor.addEventListener('input', formatarMoeda);
}

// Remove uma linha de pagamento
function removerPagamento(button) {
    button.parentElement.remove();
    atualizarValorRestante();
}

// Calcula o valor restante em tempo real
function atualizarValorRestante() {
    let valorPagoTotal = 0;
    const inputsValor = document.querySelectorAll('.valor-pago');
    
    inputsValor.forEach(input => {
        let valor = parseFloat(input.value.replace('R$ ', '').replace(/\./g, '').replace(',', '.')) || 0;
        valorPagoTotal += valor;
    });

    const restante = totalVenda - valorPagoTotal;
    valorRestanteElement.innerText = restante.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    if (Math.abs(restante) < 0.01) {
        valorRestanteElement.classList.remove('negativo');
        btnConfirmarPagamento.disabled = false;
    } else {
        valorRestanteElement.classList.add('negativo');
        btnConfirmarPagamento.disabled = true;
    }
}

// Formata o input de valor para o formato de moeda (BRL)
function formatarMoeda(event) {
    let input = event.target;
    let value = input.value.replace(/\D/g, ''); 
    if (value.length === 0) {
        input.value = '';
        return;
    }
    value = (parseInt(value) / 100).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
    input.value = 'R$ ' + value;
}

// Fecha a modal se clicar fora dela
window.onclick = function(event) {
    if (event.target == modal) {
        fecharModalPagamento();
    }
}