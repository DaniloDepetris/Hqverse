// navigation.js - Gerencia a navegação entre as páginas
document.addEventListener('DOMContentLoaded', function() {
    // Adiciona classe ativa no menu de categorias
    const categories = document.querySelectorAll('.category');
    if (categories.length > 0) {
        categories.forEach(category => {
            category.addEventListener('click', function() {
                categories.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
            });
        });
    }
    
    // Gerencia o redirecionamento para o leitor
    const readButtons = document.querySelectorAll('.btn-primary');
    readButtons.forEach(button => {
        if (button.textContent.includes('Ler agora')) {
            button.addEventListener('click', function() {
                window.location.href = 'leitor.html';
            });
        }
    });
    
    // Adiciona funcionalidade de busca
    const searchInput = document.querySelector('.search-bar input');
    const searchButton = document.querySelector('.search-bar button');
    
    if (searchInput && searchButton) {
        searchButton.addEventListener('click', function() {
            performSearch(searchInput.value);
        });
        
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch(searchInput.value);
            }
        });
    }
    
    function performSearch(query) {
        if (query.trim() !== '') {
            alert(`Buscando por: ${query}`);
            // Aqui você implementaria a busca real
        }
    }
});
