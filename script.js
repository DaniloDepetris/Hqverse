// Modal de Login/Cadastro (seleções defensivas)
const authModal = document.getElementById('authModal');
const closeBtn = document.querySelector('.close-btn');
const tabs = document.querySelectorAll('.tab');
const tabContents = document.querySelectorAll('.tab-content');

// Função para abrir/fechar modal
function toggleModal(modalId, show = true) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = show ? 'flex' : 'none';
    document.body.style.overflow = show ? 'hidden' : 'auto';
}

// Fechar modal (se existir)
if (closeBtn) {
    closeBtn.addEventListener('click', () => {
        toggleModal('authModal', false);
    });
}

// Fechar modal ao clicar fora (se o modal existir)
if (authModal) {
    window.addEventListener('click', (e) => {
        if (e.target === authModal) toggleModal('authModal', false);
    });
}

// Trocar entre abas
tabs.forEach(tab => {
    tab.addEventListener('click', () => {
        const tabId = tab.getAttribute('data-tab');

        // Remover classe active de todas as tabs e contents
        tabs.forEach(t => t.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));

        // Adicionar classe active na tab e content selecionados
        tab.classList.add('active');
        const target = document.getElementById(tabId);
        if (target) target.classList.add('active');
    });
});

// Formulário de login (se existir)
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const emailEl = document.getElementById('loginEmail');
        const passwordEl = document.getElementById('loginPassword');
        const email = emailEl ? emailEl.value : '';
        const password = passwordEl ? passwordEl.value : '';

        if (!email || !password) {
            alert('Por favor, preencha todos os campos');
            return;
        }

        alert('Login realizado com sucesso!');
        toggleModal('authModal', false);
    });
}

// Formulário de cadastro (se existir)
const signupForm = document.getElementById('signupForm');
if (signupForm) {
    signupForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const nameEl = document.getElementById('signupName');
        const emailEl = document.getElementById('signupEmail');
        const passwordEl = document.getElementById('signupPassword');
        const confirmEl = document.getElementById('signupConfirmPassword');
        const name = nameEl ? nameEl.value : '';
        const email = emailEl ? emailEl.value : '';
        const password = passwordEl ? passwordEl.value : '';
        const confirmPassword = confirmEl ? confirmEl.value : '';

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
        if (tabs[0]) tabs[0].classList.add('active');
        if (tabContents[0]) tabContents[0].classList.add('active');

        signupForm.reset();
    });
}

// Categorias - filtro
document.querySelectorAll('.category').forEach(category => {
    category.addEventListener('click', function() {
        document.querySelectorAll('.category').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        // TODO: implementar filtro via AJAX
        // evitar alert em produção
        console.log(`Filtrando por: ${this.textContent}`);
    });
});

// Barra de pesquisa
const searchBtn = document.querySelector('.search-bar button');
if (searchBtn) {
    searchBtn.addEventListener('click', function() {
        const input = document.querySelector('.search-bar input');
        const searchTerm = input ? input.value.trim() : '';
        if (searchTerm) {
            console.log(`Buscando por: ${searchTerm}`);
        }
    });
}

// Permitir busca ao pressionar Enter
const searchInput = document.querySelector('.search-bar input');
if (searchInput) {
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            if (searchBtn) searchBtn.click();
        }
    });
}

// Botões de ação nos quadrinhos
// Clicking the card should open the reader for that comic (excluding clicks on buttons inside the card)
document.querySelectorAll('.comic-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (!card) return;
        // If the click originated from a button or interactive control inside the card, ignore here
        if (e.target && (e.target.closest('button') || e.target.closest('a') || e.target.closest('.card-actions'))) return;

        // gather details to pass to the reader
        const id = card.getAttribute('data-id') || '';
        const title = card.querySelector('.comic-title')?.textContent || '';
        const cover = card.querySelector('.comic-cover')?.getAttribute('src') || '';
        // normalized progress: try data-progress attribute, then .progress-bar width
        let progress = card.getAttribute('data-progress') || (card.querySelector('.progress-bar')?.style.width || '0%');
        if (typeof progress === 'string' && progress.indexOf('%') === -1 && progress !== '') progress = `${progress}%`;

        const params = new URLSearchParams();
        if (id) params.set('comic', id);
        if (title) params.set('title', title);
        if (cover) params.set('cover', cover);
        if (progress) params.set('progress', progress);

        // navigate to reader
        window.location.href = `leitor.html?${params.toString()}`;
    });
});

// Ler agora buttons
document.querySelectorAll('.read-now').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const id = this.getAttribute('data-id') || this.closest('.comic-card')?.getAttribute('data-id');
        if (!id) {
            console.error('ID do quadrinho não encontrado');
            return;
        }
        // redirect to reader page with query param
        // include title and cover if available
        const card = this.closest('.comic-card');
        const title = card ? (card.querySelector('.comic-title')?.textContent || '') : '';
        const cover = card ? (card.querySelector('.comic-cover')?.getAttribute('src') || '') : '';
        const params = new URLSearchParams({ comic: id });
        if (title) params.set('title', title);
        if (cover) params.set('cover', cover);
        window.location.href = `leitor.html?${params.toString()}`;
    });
});

// Auto-inject read-now buttons for any comic-card that doesn't have one (useful for dynamically loaded cards)
(function injectReadNowButtons() {
    document.querySelectorAll('.comic-card').forEach((card, index) => {
        if (!card.querySelector('.read-now')) {
            const id = card.getAttribute('data-id') || String(index + 1);
            // create actions container if missing
            let actions = card.querySelector('.card-actions');
            if (!actions) {
                actions = document.createElement('div');
                actions.className = 'card-actions';
                card.appendChild(actions);
            }
            const btn = document.createElement('button');
            btn.className = 'btn btn-primary read-now';
            btn.type = 'button';
            btn.setAttribute('data-id', id);
            btn.textContent = 'Ler agora';
            actions.appendChild(btn);
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const title = card.querySelector('.comic-title')?.textContent || '';
                const cover = card.querySelector('.comic-cover')?.getAttribute('src') || '';
                const params = new URLSearchParams({ comic: id });
                if (title) params.set('title', title);
                if (cover) params.set('cover', cover);
                window.location.href = `leitor.html?${params.toString()}`;
            });
        }
    });
})();

// Botão "Ler agora" no destaque
// Alternância de tema claro/escuro (defensivo)
const themeToggleBtn = document.getElementById('themeToggle');
let darkTheme = true;
if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', function() {
        darkTheme = !darkTheme;
        if (darkTheme) {
            document.body.style.backgroundColor = '#16213e';
            document.body.style.color = '#f6f6f6';
            themeToggleBtn.textContent = '🌙';
        } else {
            document.body.style.backgroundColor = '#f6f6f6';
            document.body.style.color = '#16213e';
            themeToggleBtn.textContent = '☀️';
        }
    });
}
const featuredRead = document.querySelector('.featured-actions .btn-primary');
if (featuredRead) {
    featuredRead.addEventListener('click', function(e) {
        e.stopPropagation();
        console.log('Redirecionando para o leitor...');
    });
}

const featuredList = document.querySelector('.featured-actions .btn-outline');
if (featuredList) {
    featuredList.addEventListener('click', function(e) {
        e.stopPropagation();
        console.log('Adicionado à sua lista!');
    });
}

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

// Gerenciador de tema claro/escuro (persistência opcional)
const themeStatus = document.getElementById('themeStatus');
const body = document.body;
const savedTheme = typeof localStorage !== 'undefined' ? localStorage.getItem('theme') : null;
if (savedTheme === 'light-theme') {
    body.classList.add('light-theme');
    if (themeStatus) themeStatus.textContent = 'Modo Claro';
    if (themeToggleBtn) themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
} else {
    body.classList.remove('light-theme');
    if (themeStatus) themeStatus.textContent = 'Modo Escuro';
    if (themeToggleBtn) themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
}

if (themeToggleBtn) {
    themeToggleBtn.addEventListener('click', () => {
        body.classList.toggle('light-theme');

        if (body.classList.contains('light-theme')) {
            try { localStorage.setItem('theme', 'light-theme'); } catch (e) {}
            if (themeStatus) themeStatus.textContent = 'Modo Claro';
            themeToggleBtn.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            try { localStorage.setItem('theme', 'dark-theme'); } catch (e) {}
            if (themeStatus) themeStatus.textContent = 'Modo Escuro';
            themeToggleBtn.innerHTML = '<i class="fas fa-moon"></i>';
        }

        if (themeStatus) {
            themeStatus.style.opacity = '1';
            setTimeout(() => {
                themeStatus.style.opacity = '0.7';
            }, 1000);
        }
    });
}
        
        // Debug: Log para verificar se o script está carregando
        console.log('Script carregado com sucesso!');

        // Efeito de rolagem no header (defensivo)
        (() => {
            const header = document.querySelector('header');
            if (!header) return;
            window.addEventListener('scroll', function() {
                if (window.scrollY > 50) header.classList.add('scrolled');
                else header.classList.remove('scrolled');
            });
        })();

        // Sincronizar ícone do theme toggle (caso o botão exista)
        (function() {
            const btn = document.getElementById('themeToggle');
            if (!btn) return;
            btn.addEventListener('click', function() {
                const icon = btn.querySelector('i');
                if (!icon) return;
                if (document.body.classList.contains('light-theme')) {
                    icon.className = 'fas fa-sun';
                } else {
                    icon.className = 'fas fa-moon';
                }
            });
        })();

        // Logout: interceptar clique no link de logout e chamar endpoint
        (() => {
            const logoutLink = document.querySelector('.user-dropdown a[href="login.html"]');
            if (!logoutLink) return;
            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                // Chamar endpoint de logout
                fetch('auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'logout' })
                }).then(res => res.json()).then(data => {
                    // sempre redirecionar para a página de login
                    window.location.href = 'login.html';
                }).catch(err => {
                    // em caso de falha, ainda redirecionar (fallback)
                    console.error('Erro no logout:', err);
                    window.location.href = 'login.html';
                });
            });
        })();

        // Netflix-like row navigation: add left/right buttons to each carousel
        (function() {
            const carousels = document.querySelectorAll('.comics-carousel');
            carousels.forEach((carousel, idx) => {
                // wrap the carousel in a container to position arrows
                const outer = document.createElement('div');
                outer.className = 'row-outer';
                // create a title container if parent has section title
                const parentSection = carousel.closest('.comics-section');
                if (parentSection) {
                    const header = parentSection.querySelector('.section-title');
                    if (header) {
                        const rowTitle = document.createElement('div');
                        rowTitle.className = 'row-title';
                        const h4 = document.createElement('h4');
                        h4.textContent = header.textContent;
                        rowTitle.appendChild(h4);
                        parentSection.insertBefore(rowTitle, parentSection.firstChild);
                        // hide original title
                        header.style.display = 'none';
                    }
                }

                // insert outer before carousel and move carousel into it
                carousel.parentNode.insertBefore(outer, carousel);
                outer.appendChild(carousel);

                // left arrow
                const leftBtn = document.createElement('button');
                leftBtn.className = 'row-nav left';
                leftBtn.setAttribute('aria-label', 'Scroll left');
                leftBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
                outer.appendChild(leftBtn);

                // right arrow
                const rightBtn = document.createElement('button');
                rightBtn.className = 'row-nav right';
                rightBtn.setAttribute('aria-label', 'Scroll right');
                rightBtn.innerHTML = '<i class="fas fa-chevron-right"></i>';
                outer.appendChild(rightBtn);

                const scrollAmount = () => Math.round(carousel.clientWidth * 0.8);

                leftBtn.addEventListener('click', () => {
                    carousel.scrollBy({ left: -scrollAmount(), behavior: 'smooth' });
                });

                rightBtn.addEventListener('click', () => {
                    carousel.scrollBy({ left: scrollAmount(), behavior: 'smooth' });
                    // after scroll, try to sync any linked info (small timeout to allow scroll)
                    setTimeout(() => syncContinueInfo(carousel), 350);
                });
            });
        })();

        // Sync the Continue Watching info panel with the centered card
        function syncContinueInfo(carouselEl) {
            try {
                // find the visible center of the carousel
                const rect = carouselEl.getBoundingClientRect();
                const centerX = rect.left + rect.width / 2;
                const cards = Array.from(carouselEl.querySelectorAll('.comic-card'));
                if (!cards.length) return;
                // choose the card whose center is closest to carousel center
                let closest = null;
                let minDist = Infinity;
                cards.forEach(card => {
                    const r = card.getBoundingClientRect();
                    const cardCenter = r.left + r.width / 2;
                    const dist = Math.abs(cardCenter - centerX);
                    if (dist < minDist) { minDist = dist; closest = card; }
                });
                if (!closest) return;
                // mark centered card
                cards.forEach(c => c.classList.remove('active-large'));
                if (closest) closest.classList.add('active-large');
                const title = closest.querySelector('.comic-title')?.textContent || '';
                const meta = closest.querySelector('.comic-meta')?.textContent || '';
                const progress = closest.getAttribute('data-progress') || closest.querySelector('.progress-bar')?.style.width || '0%';
                // write into panel
                const titleEl = document.getElementById('infoTitle');
                const metaEl = document.getElementById('infoMeta');
                const progEl = document.getElementById('infoProgress');
                if (titleEl) titleEl.textContent = title;
                if (metaEl) metaEl.textContent = meta;
                if (progEl) {
                    // normalize progress value
                    let w = progress;
                    if (typeof w === 'string' && w.indexOf('%') === -1) w = `${w}%`;
                    progEl.style.width = w;
                }
            } catch (e) { console.error('syncContinueInfo error', e); }
        }

        // Attach scroll listener for continue-row carousels
        (function attachContinueSync() {
            const continueCarousels = document.querySelectorAll('.continue-row .comics-carousel');
            continueCarousels.forEach(carousel => {
                // initial sync
                syncContinueInfo(carousel);
                // debounce scroll
                let tid = null;
                carousel.addEventListener('scroll', () => {
                    if (tid) clearTimeout(tid);
                    tid = setTimeout(() => syncContinueInfo(carousel), 120);
                    // after user stops scrolling, snap the closest card into center
                    if (tid) clearTimeout(carousel._snapTid);
                    carousel._snapTid = setTimeout(() => {
                        // find closest and smooth scroll so it's exactly centered
                        const rect = carousel.getBoundingClientRect();
                        const centerX = rect.left + rect.width / 2;
                        const cards = Array.from(carousel.querySelectorAll('.comic-card'));
                        let closest = null; let minDist = Infinity;
                        cards.forEach(card => {
                            const r = card.getBoundingClientRect();
                            const cardCenter = r.left + r.width / 2;
                            const dist = Math.abs(cardCenter - centerX);
                            if (dist < minDist) { minDist = dist; closest = card; }
                        });
                        if (closest) {
                            const r = closest.getBoundingClientRect();
                            const cardCenter = r.left + r.width / 2;
                            const delta = cardCenter - centerX;
                            carousel.scrollBy({ left: delta, behavior: 'smooth' });
                            setTimeout(() => syncContinueInfo(carousel), 300);
                        }
                    }, 220);
                });
            });
        })();

// Initialize per-card small progress bars from data-progress attributes
(function initCardProgress() {
    document.querySelectorAll('.comic-card').forEach(card => {
        const pct = card.getAttribute('data-progress') || card.querySelector('.progress-bar')?.style.width || '';
        let value = '';
        if (pct) {
            value = String(pct).indexOf('%') === -1 ? `${pct}%` : pct;
        }
        // ensure progress element exists
        let container = card.querySelector('.progress');
        if (!container) {
            container = document.createElement('div');
            container.className = 'progress';
            card.appendChild(container);
        }
        let bar = container.querySelector('.progress-bar');
        if (!bar) {
            bar = document.createElement('div');
            bar.className = 'progress-bar';
            container.appendChild(bar);
        }
        if (value) bar.style.width = value;
    });
})();

// Delegated handler: replace broken comic-cover images with a placeholder (covers dynamic cases)
(function handleBrokenImages() {
    document.addEventListener('error', function(e) {
        const target = e.target;
        if (target && target.matches && target.matches('.comic-cover')) {
            if (target.dataset && target.dataset.broken) return; // already handled
            target.dataset.broken = '1';
            target.src = 'https://via.placeholder.com/380x580?text=Imagem+Indispon%C3%ADvel';
        }
    }, true);
})();

// User avatar dropdown toggle and logout handling
(function userDropdown() {
    const avatar = document.getElementById('userAvatar');
    const dropdown = avatar ? avatar.querySelector('.user-dropdown') : null;
    const logoutBtn = document.getElementById('logoutBtn');

    function closeDropdown() {
        if (dropdown) dropdown.classList.remove('open');
    }

    function openDropdown() {
        if (dropdown) dropdown.classList.add('open');
    }

    if (avatar && dropdown) {
        avatar.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdown.classList.toggle('open');
        });

        // close when clicking outside
        document.addEventListener('click', (e) => {
            if (!avatar.contains(e.target)) closeDropdown();
        });

        // close on Escape
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeDropdown(); });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            // reuse existing logout fetch path
            fetch('auth.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'logout' })
            }).then(res => res.json()).then(() => {
                window.location.href = 'login.html';
            }).catch(() => { window.location.href = 'login.html'; });
        });
    }
})();
