document.addEventListener('DOMContentLoaded', () => {

    // ── Dark mode ──────────────────────────────────────────
    const html     = document.documentElement;
    const themeBtn = document.getElementById('themeBtn');
    const iconMoon = document.getElementById('iconMoon');
    const iconSun  = document.getElementById('iconSun');

    const temaGuardado = localStorage.getItem('tema') || 'light';
    aplicarTema(temaGuardado);

    if (themeBtn) {
        themeBtn.addEventListener('click', () => {
            const nuevo = html.dataset.theme === 'dark' ? 'light' : 'dark';
            aplicarTema(nuevo);
            localStorage.setItem('tema', nuevo);
        });
    }

    function aplicarTema(tema) {
        html.dataset.theme = tema;
        if (!iconMoon || !iconSun) return;
        if (tema === 'dark') {
            iconMoon.style.display = 'none';
            iconSun.style.display  = 'inline-block';
        } else {
            iconMoon.style.display = 'inline-block';
            iconSun.style.display  = 'none';
        }
    }

    // ── Panel catálogo ─────────────────────────────────────
    const catBtn       = document.getElementById('catBtn');
    const panel        = document.getElementById('catalogoPanel');
    const overlay      = document.getElementById('navOverlay');
    const catalogoSubs = document.getElementById('catalogoSubs');

    if (catBtn && panel) {
        catBtn.addEventListener('click', () => {
            const abierto = !panel.hidden;
            abierto ? cerrarPanel() : abrirPanel();
        });

        overlay?.addEventListener('click', cerrarPanel);

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') cerrarPanel();
        });

        // Hover en categorías → mostrar subcategorías
        const cats = document.querySelectorAll('.catalogo-panel__cat');
        cats.forEach(cat => {
            cat.addEventListener('mouseenter', () => {
                cats.forEach(c => c.classList.remove('activo'));
                cat.classList.add('activo');
                mostrarSubs(parseInt(cat.dataset.id), cat.dataset.slug);
            });
        });

        // Activar primera categoría por defecto al abrir
        if (cats.length > 0) {
            const primera = cats[0];
            primera.classList.add('activo');
            mostrarSubs(parseInt(primera.dataset.id), primera.dataset.slug);
        }
    }

    function abrirPanel() {
        panel.hidden   = false;
        overlay.hidden = false;
        catBtn.classList.add('open');
        catBtn.setAttribute('aria-expanded', 'true');
        // Forzar reflow para animación
        panel.getBoundingClientRect();
        panel.classList.add('open');
    }

    function cerrarPanel() {
        panel.classList.remove('open');
        catBtn.classList.remove('open');
        catBtn.setAttribute('aria-expanded', 'false');
        overlay.hidden = true;
        setTimeout(() => { panel.hidden = true; }, 280);
    }

    function mostrarSubs(categoriaId, categoriaSlug) {
        if (!window.SUBCATEGORIAS || !catalogoSubs) return;

        const subs = window.SUBCATEGORIAS.filter(s => s.categoria_id === categoriaId);

        if (subs.length === 0) {
            catalogoSubs.innerHTML = `
                <p class="catalogo-panel__subs-titulo">Sin subcategorías</p>
            `;
            return;
        }

        const titulo = document.querySelector(
            `.catalogo-panel__cat[data-id="${categoriaId}"]`
        )?.querySelector('span')?.textContent || '';

        const items = subs.map(s => `
            <a href="/categoria-producto/subcategoria?categoria=${categoriaSlug}&subcategoria=${s.slug}"
               class="catalogo-panel__sub">
                ${s.nombre}
            </a>
        `).join('');

        catalogoSubs.innerHTML = `
            <p class="catalogo-panel__subs-titulo">${titulo}</p>
            <div class="catalogo-panel__subs-grid">${items}</div>
        `;

        // Cerrar panel al hacer click en subcategoría
        catalogoSubs.querySelectorAll('.catalogo-panel__sub').forEach(el => {
            el.addEventListener('click', cerrarPanel);
        });
    }

    // ── Dropdown usuario ───────────────────────────────────
    const userMenu = document.getElementById('userMenu');

    if (userMenu) {
        const userBtn      = userMenu.querySelector('.nav__btn--user');
        const userDropdown = userMenu.querySelector('.nav__dropdown');

        userBtn?.addEventListener('click', e => {
            e.stopPropagation();
            const abierto = !userDropdown.hidden;
            userDropdown.hidden = abierto;
            userBtn.setAttribute('aria-expanded', String(!abierto));
        });

        document.addEventListener('click', () => {
            if (userDropdown) userDropdown.hidden = true;
        });
    }

    // ── Buscador en tiempo real ────────────────────────────
    const inputBuscador    = document.getElementById('buscador-input');
    const resultadosBuscador = document.getElementById('buscador-resultados');

    if (inputBuscador && resultadosBuscador) {
        let timeoutBuscador;

        inputBuscador.addEventListener('input', () => {
            clearTimeout(timeoutBuscador);
            const q = inputBuscador.value.trim();

            if (q.length < 2) {
                resultadosBuscador.hidden = true;
                return;
            }

            timeoutBuscador = setTimeout(() => buscar(q), 300);
        });

        inputBuscador.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                resultadosBuscador.hidden = true;
                inputBuscador.blur();
            }
        });

        document.addEventListener('click', e => {
            if (!inputBuscador.contains(e.target) && !resultadosBuscador.contains(e.target)) {
                resultadosBuscador.hidden = true;
            }
        });
    }

    async function buscar(q) {
        try {
            const res  = await fetch(`/buscar?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            renderBuscador(data);
        } catch {
            resultadosBuscador.hidden = true;
        }
    }

    function renderBuscador(productos) {
        if (!resultadosBuscador) return;

        if (productos.length === 0) {
            resultadosBuscador.innerHTML = `<p class="buscador__vacio">Sin resultados</p>`;
            resultadosBuscador.hidden = false;
            return;
        }

        resultadosBuscador.innerHTML = productos.map(p => `
            <a href="${p.url}" class="buscador__item">
                <img src="/img/productos/${p.imagen}" alt="${p.nombre}">
                <span class="buscador__item-nombre">${p.nombre}</span>
                <span class="buscador__item-precio">$${Number(p.precio).toLocaleString('es-AR')}</span>
            </a>
        `).join('');

        resultadosBuscador.hidden = false;
    }

    // ── Contador carrito (actualiza badge desde JS) ────────
    window.actualizarContadorCarrito = function(total) {
        const badge = document.getElementById('contadorCarrito');
        if (!badge) return;
        badge.textContent = total;
        badge.hidden = total === 0;
    };

});
// Patch already loaded — search button click triggers search
document.addEventListener('DOMContentLoaded', () => {
    const btnBuscar = document.getElementById('btnBuscar');
    const inputB    = document.getElementById('buscador-input');
    if (btnBuscar && inputB) {
        btnBuscar.addEventListener('click', () => {
            const q = inputB.value.trim();
            if (q.length >= 2) {
                fetch(`/buscar?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(data => {
                        const res = document.getElementById('buscador-resultados');
                        if (!res) return;
                        if (data.length === 0) {
                            res.innerHTML = '<p class="buscador__vacio">Sin resultados</p>';
                        } else {
                            res.innerHTML = data.map(p => `
                                <a href="${p.url}" class="buscador__item">
                                    <img src="/img/productos/${p.imagen}" alt="${p.nombre}">
                                    <span class="buscador__item-nombre">${p.nombre}</span>
                                    <span class="buscador__item-precio">$${Number(p.precio).toLocaleString('es-AR')}</span>
                                </a>
                            `).join('');
                        }
                        res.hidden = false;
                    });
            } else {
                inputB.focus();
            }
        });
    }
});
