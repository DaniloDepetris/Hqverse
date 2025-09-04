// Modal de Login/Cadastro
const authModal = document.getElementById('authModal');
const closeBtn = document.querySelector('.close-btn');
const tabs = document.querySelectorAll('.tab');
const tabContents = document.querySelectorAll('.tab-content');

// Função para abrir/fechar modal
function toggleModal(modalId, show = true) {
    const modal = document.getElementById(modalId);
    modal.style.display = show ? 'flex' : 'none';
    document.body.style.overflow = show ? 'hidden' : 'auto';
}

// Fechar modal
closeBtn.addEventListener('click', () => {
    toggleModal('authModal', false);
});

// Fechar modal ao clicar fora
window.addEventListener('click', (e) => {
    if (e.target === authModal) toggleModal('authModal', false);
});

// Trocar entre abas
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const tabId = tab.getAttribute('data-tab');

        // Remover classe active de todas as tabs e contents
        tabs.forEach(t => t.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));

        // Adicionar classe active na tab e content selecionados
        tab.classList.add('active');
        document.getElementById(tabId).classList.add('active');
    });
});

// Formulário de login
document.getElementById('loginForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    if (!email || !password) {
        alert('Por favor, preencha todos os campos');
        return;
    }

    alert('Login realizado com sucesso!');
    toggleModal('authModal', false);
});

// Formulário de cadastro
document.getElementById('signupForm').addEventListener('submit', (e) => {
    e.preventDefault();
    const name = document.getElementById('signupName').value;
    const email = document.getElementById('signupEmail').value;
    const password = document.getElementById('signupPassword').value;
    const confirmPassword = document.getElementById('signupConfirmPassword').value;

    if (!name || !email || !password || !confirmPassword) {
        alert('Por favor, preencha todos os campos');
        return;
    }

    if (password !== confirmPassword) {
        alert('As senhas não coincidem!');
        return;
    }

    if (password.length < 6) {
        alert('A senha deve ter pelo menos 6 caracteres');
        return;
    }

    alert('Conta criada com sucesso! Faça login.');

    tabs.forEach(t => t.classList.remove('active'));
    tabContents.forEach(c => c.classList.remove('active'));
    tabs[0].classList.add('active');
    tabContents[0].classList.add('active');

    document.getElementById('signupForm').reset();
});

// Categorias - filtro
document.querySelectorAll('.category').forEach(category => {
    category.addEventListener('click', function() {
        document.querySelectorAll('.category').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        alert(`Filtrando por: ${this.textContent}`);
    });
});

// Barra de pesquisa
document.querySelector('.search-bar button').addEventListener('click', function() {
    const searchTerm = document.querySelector('.search-bar input').value;
    if (searchTerm) {
        alert(`Buscando por: ${searchTerm}`);
    }
});

// Permitir busca ao pressionar Enter
document.querySelector('.search-bar input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.querySelector('.search-bar button').click();
    }
});

// Botões de ação nos quadrinhos
document.querySelectorAll('.comic-card').forEach(card => {
    card.addEventListener('click', function() {
        const title = this.querySelector('.comic-title').textContent;
        alert(`Abrindo quadrinho: ${title}`);
    });
});

// Botão "Ler agora" no destaque
document.querySelector('.featured-actions .btn-primary').addEventListener('click', function(e) {
    e.stopPropagation();
    alert('Redirecionando para o leitor...');
});

// Botão "Minha lista" no destaque
document.querySelector('.featured-actions .btn-outline').addEventListener('click', function(e) {
    e.stopPropagation();
    alert('Adicionado à sua lista!');
});
