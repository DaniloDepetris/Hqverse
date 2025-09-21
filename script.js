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
// Alternância de tema claro/escuro
const themeToggle = document.getElementById('themeToggle');
let darkTheme = true;

themeToggle.addEventListener('click', function() {
    darkTheme = !darkTheme;
    if (darkTheme) {
        document.body.style.backgroundColor = '#16213e';
        document.body.style.color = '#f6f6f6';
        themeToggle.textContent = '🌙';
    } else {
        document.body.style.backgroundColor = '#f6f6f6';
        document.body.style.color = '#16213e';
        themeToggle.textContent = '☀️';
    }
});
document.querySelector('.featured-actions .btn-primary').addEventListener('click', function(e) {
    e.stopPropagation();
    alert('Redirecionando para o leitor...');
});

// Botão "Minha lista" no destaque
document.querySelector('.featured-actions .btn-outline').addEventListener('click', function(e) {
    e.stopPropagation();
    alert('Adicionado à sua lista!');
});

// Controle do menu mobile
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const searchBar = document.getElementById('searchBar');
    
    if (mobileMenuBtn && searchBar) {
        mobileMenuBtn.addEventListener('click', function() {
            searchBar.classList.toggle('active');
            
            if (searchBar.classList.contains('active')) {
                mobileMenuBtn.innerHTML = '<i class="fas fa-times"></i>';
            } else {
                mobileMenuBtn.innerHTML = '<i class="fas fa-bars"></i>';
            }
        });
    }
    
    // Controle do tema claro/escuro
    const themeToggle = document.getElementById('themeToggle');
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('light-theme');
            
            if (document.body.classList.contains('light-theme')) {
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            } else {
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            }
        });
    }
    
    // Detecção de redimensionamento para debug
    function logLayoutChange() {
        const width = window.innerWidth;
        let layoutType = "";
        
        if (width >= 1024) {
            layoutType = "Desktop (>= 1024px)";
        } else if (width >= 768) {
            layoutType = "Tablet (768px - 1023px)";
        } else if (width >= 576) {
            layoutType = "Smartphone Grande (576px - 767px)";
        } else {
            layoutType = "Smartphone Pequeno (< 576px)";
        }
        
        console.log(`Layout: ${layoutType} - Largura: ${width}px`);
    }
    
    window.addEventListener('resize', logLayoutChange);
    logLayoutChange();
