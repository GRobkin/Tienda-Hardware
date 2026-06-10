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

        const cats = document.querySelectorAll('.catalogo-panel__cat');
        cats.forEach(cat => {
            cat.addEventListener('mouseenter', () => {
                cats.forEach(c => c.classList.remove('activo'));
                cat.classList.add('activo');
                mostrarSubs(parseInt(cat.dataset.id), cat.dataset.slug);
            });
        });

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
            catalogoSubs.innerHTML = `<p class="catalogo-panel__subs-titulo">Sin subcategorías</p>`;
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
    const inputBuscador      = document.getElementById('buscador-input');
    const resultadosBuscador = document.getElementById('buscador-resultados');
    const btnBuscar          = document.getElementById('btnBuscar');

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

        // Buscar al presionar Enter
        inputBuscador.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                const q = inputBuscador.value.trim();
                if (q.length >= 2) buscar(q);
            }
            if (e.key === 'Escape') {
                resultadosBuscador.hidden = true;
                inputBuscador.blur();
            }
        });

        // Buscar al clickear la lupa
        btnBuscar?.addEventListener('click', () => {
            const q = inputBuscador.value.trim();
            if (q.length >= 2) buscar(q);
            else inputBuscador.focus();
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

    // ── Contador carrito ───────────────────────────────────
    window.actualizarContadorCarrito = function(total) {
        const badge = document.getElementById('contadorCarrito');
        if (!badge) return;
        badge.textContent = total;
        badge.hidden = total === 0;
    };

    // ── Agregar al carrito ─────────────────────────────────
    document.addEventListener('click', e => {
        const btn = e.target.closest('.agregar-carrito');
        if (!btn) return;

        const productoId = btn.dataset.id;
        if (!productoId) return;

        btn.disabled = true;

        fetch('/carrito/agregar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ producto_id: productoId, cantidad: 1 })
        })
        .then(r => r.json())
        .then(data => {
            if (data.total !== undefined) {
                window.actualizarContadorCarrito(data.total);
            }
            btn.textContent = '¡Agregado!';
            setTimeout(() => {
                btn.textContent = 'Agregar al carrito';
                btn.disabled = false;
            }, 1500);
        })
        .catch(() => { btn.disabled = false; });
    });

    // ── Carrito interactivo (cantidad y eliminar) ──────────
    const tablaCarrito = document.getElementById('tablaCarrito');

    if (tablaCarrito) {
        tablaCarrito.addEventListener('click', e => {
            const btnMas    = e.target.closest('.carrito__btn-mas');
            const btnMenos  = e.target.closest('.carrito__btn-menos');
            const btnElim   = e.target.closest('.carrito__btn-eliminar');

            if (btnMas) cambiarCantidad(btnMas.dataset.id, 1);
            if (btnMenos) cambiarCantidad(btnMenos.dataset.id, -1);
            if (btnElim) eliminarItem(btnElim.dataset.id);
        });
    }

    function cambiarCantidad(productoId, delta) {
        fetch('/carrito/cantidad', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ producto_id: productoId, delta })
        })
        .then(r => r.json())
        .then(data => {
            if (data.reload) location.reload();
            if (data.total !== undefined) window.actualizarContadorCarrito(data.total);
        })
        .catch(() => {});
    }

    function eliminarItem(productoId) {
        fetch('/carrito/eliminar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ producto_id: productoId })
        })
        .then(r => r.json())
        .then(data => {
            if (data.reload) location.reload();
            if (data.total !== undefined) window.actualizarContadorCarrito(data.total);
        })
        .catch(() => {});
    }

    // ── Validación login ───────────────────────────────────
    const formLogin = document.getElementById('formLogin');

    if (formLogin) {
        const inputEmail = document.getElementById('email');
        const inputPass  = document.getElementById('password');
        const errorEmail = document.getElementById('errorEmail');
        const errorPass  = document.getElementById('errorPass');
        const togglePass = document.getElementById('togglePass');

        togglePass?.addEventListener('click', () => {
            inputPass.type = inputPass.type === 'password' ? 'text' : 'password';
        });

        inputEmail?.addEventListener('blur', () => validarEmail());
        inputPass?.addEventListener('blur',  () => validarPass());

        formLogin.addEventListener('submit', e => {
            const okEmail = validarEmail();
            const okPass  = validarPass();
            if (!okEmail || !okPass) e.preventDefault();
        });

        function validarEmail() {
            const val = inputEmail.value.trim();
            if (!val) {
                mostrarError(inputEmail, errorEmail, 'El correo es obligatorio');
                return false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                mostrarError(inputEmail, errorEmail, 'El correo no es válido');
                return false;
            }
            limpiarError(inputEmail, errorEmail);
            return true;
        }

        function validarPass() {
            const val = inputPass.value;
            if (!val) {
                mostrarError(inputPass, errorPass, 'La contraseña es obligatoria');
                return false;
            }
            limpiarError(inputPass, errorPass);
            return true;
        }

        function mostrarError(input, span, msg) {
            input.classList.add('auth__input--error');
            span.textContent = msg;
        }

        function limpiarError(input, span) {
            input.classList.remove('auth__input--error');
            span.textContent = '';
        }
    }

    // ── Validación registro ────────────────────────────────
    const formRegistro = document.getElementById('formRegistro');

    if (formRegistro) {
        const inputEmail2  = document.getElementById('email');
        const inputPass2   = document.getElementById('password');
        const inputPass2c  = document.getElementById('password_confirm');
        const errorEmail2  = document.getElementById('errorEmail');
        const errorPass2   = document.getElementById('errorPass');
        const errorPass2c  = document.getElementById('errorPassConfirm');
        const togglePass2  = document.getElementById('togglePass');
        const togglePass2c = document.getElementById('togglePassConfirm');

        togglePass2?.addEventListener('click', () => {
            inputPass2.type = inputPass2.type === 'password' ? 'text' : 'password';
        });

        togglePass2c?.addEventListener('click', () => {
            inputPass2c.type = inputPass2c.type === 'password' ? 'text' : 'password';
        });

        inputEmail2?.addEventListener('blur', () => validarEmailReg());
        inputPass2?.addEventListener('blur',  () => validarPassReg());
        inputPass2c?.addEventListener('blur', () => validarPassConfirm());

        formRegistro.addEventListener('submit', e => {
            const ok1 = validarEmailReg();
            const ok2 = validarPassReg();
            const ok3 = validarPassConfirm();
            if (!ok1 || !ok2 || !ok3) e.preventDefault();
        });

        function validarEmailReg() {
            const val = inputEmail2.value.trim();
            if (!val) {
                mostrarErrorReg(inputEmail2, errorEmail2, 'El correo es obligatorio');
                return false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                mostrarErrorReg(inputEmail2, errorEmail2, 'El correo no es válido');
                return false;
            }
            limpiarErrorReg(inputEmail2, errorEmail2);
            return true;
        }

        function validarPassReg() {
            const val = inputPass2.value;
            if (!val) {
                mostrarErrorReg(inputPass2, errorPass2, 'La contraseña es obligatoria');
                return false;
            }
            if (val.length < 6) {
                mostrarErrorReg(inputPass2, errorPass2, 'Mínimo 6 caracteres');
                return false;
            }
            limpiarErrorReg(inputPass2, errorPass2);
            return true;
        }

        function validarPassConfirm() {
            if (!inputPass2c) return true;
            const val  = inputPass2c.value;
            const orig = inputPass2.value;
            if (!val) {
                mostrarErrorReg(inputPass2c, errorPass2c, 'Repetí la contraseña');
                return false;
            }
            if (val !== orig) {
                mostrarErrorReg(inputPass2c, errorPass2c, 'Las contraseñas no coinciden');
                return false;
            }
            limpiarErrorReg(inputPass2c, errorPass2c);
            return true;
        }

        function mostrarErrorReg(input, span, msg) {
            input?.classList.add('auth__input--error');
            if (span) span.textContent = msg;
        }

        function limpiarErrorReg(input, span) {
            input?.classList.remove('auth__input--error');
            if (span) span.textContent = '';
        }
    }

});

// ── Slider de banners ──────────────────────────────────
const sliderTrack = document.getElementById('sliderTrack');
const sliderDots  = document.getElementById('sliderDots');

if (sliderTrack) {
    const slides = sliderTrack.querySelectorAll('.slider__slide');
    const total  = slides.length;
    let current  = 0;
    let timer;

    const dots = Array.from({ length: total }, (_, i) => {
        const d = document.createElement('button');
        d.className = 'slider__dot' + (i === 0 ? ' activo' : '');
        d.addEventListener('click', () => goTo(i));
        sliderDots.appendChild(d);
        return d;
    
    // ── Validación olvide contraseña ──────────────────────
    const formOlvide = document.getElementById('formOlvide');

    if (formOlvide) {
        const inputEmail = document.getElementById('email');
        const errorEmail = document.getElementById('errorEmail');

        inputEmail?.addEventListener('blur', validarEmailOlvide);

        formOlvide.addEventListener('submit', e => {
            if (!validarEmailOlvide()) e.preventDefault();
        });

        function validarEmailOlvide() {
            const val = inputEmail.value.trim();
            if (!val) {
                inputEmail.classList.add('auth__input--error');
                errorEmail.textContent = 'El correo es obligatorio';
                return false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                inputEmail.classList.add('auth__input--error');
                errorEmail.textContent = 'El correo no es válido';
                return false;
            }
            inputEmail.classList.remove('auth__input--error');
            errorEmail.textContent = '';
            return true;
        }
    }

    // ── Validación restablecer contraseña ─────────────────
    const formRestablecer = document.getElementById('formRestablecer');

    if (formRestablecer) {
        const inputPass  = document.getElementById('password');
        const inputPass2 = document.getElementById('password2');
        const errorPass  = document.getElementById('errorPass');
        const errorPass2 = document.getElementById('errorPass2');
        const toggle1    = document.getElementById('togglePass');
        const toggle2    = document.getElementById('togglePass2');

        toggle1?.addEventListener('click', () => {
            inputPass.type = inputPass.type === 'password' ? 'text' : 'password';
        });
        toggle2?.addEventListener('click', () => {
            inputPass2.type = inputPass2.type === 'password' ? 'text' : 'password';
        });

        inputPass?.addEventListener('blur',  validarPassR);
        inputPass2?.addEventListener('blur', validarPass2R);

        formRestablecer.addEventListener('submit', e => {
            const ok1 = validarPassR();
            const ok2 = validarPass2R();
            if (!ok1 || !ok2) e.preventDefault();
        });

        function validarPassR() {
            const val = inputPass.value;
            if (!val) {
                inputPass.classList.add('auth__input--error');
                errorPass.textContent = 'La contraseña es obligatoria';
                return false;
            }
            if (val.length < 6) {
                inputPass.classList.add('auth__input--error');
                errorPass.textContent = 'Mínimo 6 caracteres';
                return false;
            }
            inputPass.classList.remove('auth__input--error');
            errorPass.textContent = '';
            return true;
        }

        function validarPass2R() {
            const val  = inputPass2.value;
            const val1 = inputPass.value;
            if (!val) {
                inputPass2.classList.add('auth__input--error');
                errorPass2.textContent = 'Repetí la contraseña';
                return false;
            }
            if (val !== val1) {
                inputPass2.classList.add('auth__input--error');
                errorPass2.textContent = 'Las contraseñas no coinciden';
                return false;
            }
            inputPass2.classList.remove('auth__input--error');
            errorPass2.textContent = '';
            return true;
        }
    }

    // ── Validación registro ───────────────────────────────
    const formRegistro = document.getElementById('formRegistro');

    if (formRegistro) {
        const iNombre   = document.getElementById('nombre');
        const iApellido = document.getElementById('apellido');
        const iEmail    = document.getElementById('email');
        const iPass     = document.getElementById('password');
        const iPass2    = document.getElementById('password2');
        const eNombre   = document.getElementById('errorNombre');
        const eApellido = document.getElementById('errorApellido');
        const eEmail    = document.getElementById('errorEmail');
        const ePass     = document.getElementById('errorPass');
        const ePass2    = document.getElementById('errorPass2');
        const tPass     = document.getElementById('togglePass');
        const tPass2    = document.getElementById('togglePass2');

        tPass?.addEventListener('click',  () => { iPass.type  = iPass.type  === 'password' ? 'text' : 'password'; });
        tPass2?.addEventListener('click', () => { iPass2.type = iPass2.type === 'password' ? 'text' : 'password'; });

        iNombre?.addEventListener('blur',   () => campo(iNombre,   eNombre,   'El nombre es obligatorio'));
        iApellido?.addEventListener('blur',  () => campo(iApellido, eApellido, 'El apellido es obligatorio'));
        iEmail?.addEventListener('blur',     () => campoEmail());
        iPass?.addEventListener('blur',      () => campoPass());
        iPass2?.addEventListener('blur',     () => campoPass2());

        formRegistro.addEventListener('submit', e => {
            const ok = [
                campo(iNombre,   eNombre,   'El nombre es obligatorio'),
                campo(iApellido, eApellido, 'El apellido es obligatorio'),
                campoEmail(),
                campoPass(),
                campoPass2()
            ];
            if (ok.includes(false)) e.preventDefault();
        });

        function campo(input, span, msg) {
            if (!input.value.trim()) {
                input.classList.add('auth__input--error');
                span.textContent = msg;
                return false;
            }
            input.classList.remove('auth__input--error');
            span.textContent = '';
            return true;
        }

        function campoEmail() {
            const val = iEmail.value.trim();
            if (!val) { iEmail.classList.add('auth__input--error'); eEmail.textContent = 'El correo es obligatorio'; return false; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) { iEmail.classList.add('auth__input--error'); eEmail.textContent = 'El correo no es válido'; return false; }
            iEmail.classList.remove('auth__input--error'); eEmail.textContent = ''; return true;
        }

        function campoPass() {
            const val = iPass.value;
            if (!val) { iPass.classList.add('auth__input--error'); ePass.textContent = 'La contraseña es obligatoria'; return false; }
            if (val.length < 6) { iPass.classList.add('auth__input--error'); ePass.textContent = 'Mínimo 6 caracteres'; return false; }
            iPass.classList.remove('auth__input--error'); ePass.textContent = ''; return true;
        }

        function campoPass2() {
            const val = iPass2.value;
            if (!val) { iPass2.classList.add('auth__input--error'); ePass2.textContent = 'Repetí la contraseña'; return false; }
            if (val !== iPass.value) { iPass2.classList.add('auth__input--error'); ePass2.textContent = 'Las contraseñas no coinciden'; return false; }
            iPass2.classList.remove('auth__input--error'); ePass2.textContent = ''; return true;
        }
    }

});

    function goTo(n) {
        current = (n + total) % total;
        sliderTrack.style.transform = `translateX(-${current * 100}%)`;
        dots.forEach((d, i) => d.classList.toggle('activo', i === current));
        resetTimer();
    }

    function resetTimer() {
        clearInterval(timer);
        timer = setInterval(() => goTo(current + 1), 4500);
    }

    document.getElementById('sliderPrev')
        ?.addEventListener('click', () => goTo(current - 1));
    document.getElementById('sliderNext')
        ?.addEventListener('click', () => goTo(current + 1));

    resetTimer();
}
