document.addEventListener('DOMContentLoaded', () => {

    /* Helpers */

    // POST con cuerpo form-urlencoded (llega como $_POST en PHP).
    // Adjunta siempre el token CSRF del <meta name="csrf">.
    function postForm(url, datos) {
        const csrf = document.querySelector('meta[name="csrf"]')?.content || '';
        return fetch(url, {
            method: 'POST',
            body: new URLSearchParams({ ...datos, csrf })
        }).then(r => r.json());
    }

    function marcarError(input, span, msg) {
        input?.classList.add('auth__input--error', 'campo__input--error');
        if (span) span.textContent = msg;
    }

    function limpiarError(input, span) {
        input?.classList.remove('auth__input--error', 'campo__input--error');
        if (span) span.textContent = '';
    }

    const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Toast (SweetAlert2) con fallback silencioso si la librería no cargó
    const Toast = window.Swal
        ? Swal.mixin({
            toast: true,
            position: 'top-end',
            timer: 2200,
            timerProgressBar: true,
            showConfirmButton: false
        })
        : null;

    function avisar(icon, title) {
        if (Toast) Toast.fire({ icon, title });
    }

    // Confirmación bonita (SweetAlert2) con fallback a confirm() nativo
    function confirmarAccion(mensaje) {
        if (window.Swal) {
            return Swal.fire({
                title: mensaje,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'swal-tienda' }
            }).then(r => r.isConfirmed);
        }
        return Promise.resolve(window.confirm(mensaje));
    }

    // Valida un campo de texto obligatorio. Devuelve true si es válido.
    function validarRequerido(input, span, msg) {
        if (!input) return true;
        if (!input.value.trim()) { marcarError(input, span, msg); return false; }
        limpiarError(input, span);
        return true;
    }

    function validarEmailCampo(input, span) {
        if (!input) return true;
        const val = input.value.trim();
        if (!val) { marcarError(input, span, 'El correo es obligatorio'); return false; }
        if (!EMAIL_RE.test(val)) { marcarError(input, span, 'El correo no es válido'); return false; }
        limpiarError(input, span);
        return true;
    }

    function validarPasswordCampo(input, span, minimo = 6) {
        if (!input) return true;
        if (!input.value) { marcarError(input, span, 'La contraseña es obligatoria'); return false; }
        if (input.value.length < minimo) { marcarError(input, span, `Mínimo ${minimo} caracteres`); return false; }
        limpiarError(input, span);
        return true;
    }

    function validarCoincidencia(input, original, span) {
        if (!input) return true;
        if (!input.value) { marcarError(input, span, 'Repetí la contraseña'); return false; }
        if (input.value !== original.value) { marcarError(input, span, 'Las contraseñas no coinciden'); return false; }
        limpiarError(input, span);
        return true;
    }

    function togglePassword(botonId, inputEl) {
        document.getElementById(botonId)?.addEventListener('click', () => {
            if (!inputEl) return;
            inputEl.type = inputEl.type === 'password' ? 'text' : 'password';
        });
    }

    /* Dark mode */
    const html = document.documentElement;
    const themeBtn = document.getElementById('themeBtn');
    const iconMoon = document.getElementById('iconMoon');
    const iconSun = document.getElementById('iconSun');

    aplicarTema(localStorage.getItem('tema') || 'light');

    themeBtn?.addEventListener('click', () => {
        const nuevo = html.dataset.theme === 'dark' ? 'light' : 'dark';
        aplicarTema(nuevo);
        localStorage.setItem('tema', nuevo);
    });

    function aplicarTema(tema) {
        html.dataset.theme = tema;
        if (!iconMoon || !iconSun) return;
        iconMoon.style.display = tema === 'dark' ? 'none' : 'inline-block';
        iconSun.style.display = tema === 'dark' ? 'inline-block' : 'none';
    }

    /* Panel catálogo */
    const catBtn = document.getElementById('catBtn');
    const panel = document.getElementById('catalogoPanel');
    const overlay = document.getElementById('navOverlay');
    const catalogoSubs = document.getElementById('catalogoSubs');

    if (catBtn && panel) {
        catBtn.addEventListener('click', () => {
            panel.hidden ? abrirPanel() : cerrarPanel();
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
            cats[0].classList.add('activo');
            mostrarSubs(parseInt(cats[0].dataset.id), cats[0].dataset.slug);
        }
    }

    function abrirPanel() {
        panel.hidden = false;
        overlay.hidden = false;
        catBtn.classList.add('open');
        catBtn.setAttribute('aria-expanded', 'true');
        panel.getBoundingClientRect();
        panel.classList.add('open');
    }

    function cerrarPanel() {
        if (!panel || panel.hidden) return;
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
            `.catalogo-panel__cat[data-id="${categoriaId}"] span`
        )?.textContent || '';

        catalogoSubs.innerHTML = '';

        const tituloEl = document.createElement('p');
        tituloEl.className = 'catalogo-panel__subs-titulo';
        tituloEl.textContent = titulo;

        const verTodo = document.createElement('a');
        verTodo.className = 'catalogo-panel__sub catalogo-panel__sub--todo';
        verTodo.href = `/categoria-producto/categoria?categoria=${encodeURIComponent(categoriaSlug)}`;
        verTodo.textContent = 'Ver todo →';

        const grid = document.createElement('div');
        grid.className = 'catalogo-panel__subs-grid';

        subs.forEach(s => {
            const a = document.createElement('a');
            a.className = 'catalogo-panel__sub';
            a.href = `/categoria-producto/subcategoria?categoria=${encodeURIComponent(categoriaSlug)}&subcategoria=${encodeURIComponent(s.slug)}`;
            a.textContent = s.nombre;
            a.addEventListener('click', cerrarPanel);
            grid.appendChild(a);
        });

        grid.appendChild(verTodo);
        verTodo.addEventListener('click', cerrarPanel);

        catalogoSubs.appendChild(tituloEl);
        catalogoSubs.appendChild(grid);
    }

    /* Dropdown usuario */
    const userMenu = document.getElementById('userMenu');

    if (userMenu) {
        const userBtn = userMenu.querySelector('.nav__btn--user');
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

    /* Buscador en tiempo real */
    const inputBuscador = document.getElementById('buscador-input');
    const resultadosBuscador = document.getElementById('buscador-resultados');
    const btnBuscar = document.getElementById('btnBuscar');

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
            if (e.key === 'Enter') {
                e.preventDefault();
                const q = inputBuscador.value.trim();
                if (q.length >= 2) buscar(q);
            }
            if (e.key === 'Escape') {
                resultadosBuscador.hidden = true;
                inputBuscador.blur();
            }
        });

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
            const res = await fetch(`/buscar?q=${encodeURIComponent(q)}`);
            const data = await res.json();
            renderBuscador(data);
        } catch {
            resultadosBuscador.hidden = true;
        }
    }

    // Render seguro (sin innerHTML con datos de la BD)
    function renderBuscador(productos) {
        if (!resultadosBuscador) return;
        resultadosBuscador.innerHTML = '';

        if (productos.length === 0) {
            const p = document.createElement('p');
            p.className = 'buscador__vacio';
            p.textContent = 'Sin resultados';
            resultadosBuscador.appendChild(p);
            resultadosBuscador.hidden = false;
            return;
        }

        productos.forEach(prod => {
            const a = document.createElement('a');
            a.href = prod.url;
            a.className = 'buscador__item';

            const img = document.createElement('img');
            img.src = prod.imagen; // ya viene como URL lista desde el servidor
            img.alt = prod.nombre;
            img.onerror = () => { img.onerror = null; img.src = '/img/placeholder.svg'; };

            const info = document.createElement('span');
            info.className = 'buscador__item-info';

            const nombre = document.createElement('span');
            nombre.className = 'buscador__item-nombre';
            nombre.textContent = prod.nombre;

            const cat = document.createElement('span');
            cat.className = 'buscador__item-cat';
            cat.textContent = prod.categoria || '';

            const precio = document.createElement('span');
            precio.className = 'buscador__item-precio';
            precio.textContent = 'US$ ' + Number(prod.precio).toLocaleString('es-UY', { minimumFractionDigits: 2 });

            info.appendChild(nombre);
            if (prod.categoria) info.appendChild(cat);
            a.appendChild(img);
            a.appendChild(info);
            a.appendChild(precio);
            resultadosBuscador.appendChild(a);
        });

        resultadosBuscador.hidden = false;
    }

    /* Carrito — contador */
    window.actualizarContadorCarrito = function (total) {
        const badge = document.getElementById('contadorCarrito');
        if (!badge) return;
        badge.textContent = total;
        badge.hidden = total === 0;
    };

    /* Carrito — agregar (botones .agregar-carrito en toda la web) */
    document.addEventListener('click', e => {
        const btn = e.target.closest('.agregar-carrito');
        if (!btn) return;

        const id = btn.dataset.id;
        if (!id) return;

        // Si el botón apunta a un input de cantidad (página de producto), usarlo
        let cantidad = 1;
        if (btn.dataset.cantidad) {
            const input = document.querySelector(btn.dataset.cantidad);
            cantidad = Math.max(1, parseInt(input?.value, 10) || 1);
        }

        const textoOriginal = btn.textContent;
        btn.disabled = true;

        postForm('/carrito/agregar', { id, cantidad })
            .then(data => {
                if (data.ok) {
                    window.actualizarContadorCarrito(data.total_items);
                    avisar('success', 'Producto agregado al carrito');
                    btn.textContent = '¡Agregado!';
                } else {
                    avisar('error', data.mensaje || 'No se pudo agregar');
                    btn.textContent = textoOriginal;
                }
                setTimeout(() => {
                    btn.textContent = textoOriginal;
                    btn.disabled = false;
                }, 1500);
            })
            .catch(() => {
                avisar('error', 'No se pudo agregar el producto');
                btn.textContent = textoOriginal;
                btn.disabled = false;
            });
    });

    /* Carrito — stepper genérico de cantidad (.cantidad) */
    document.addEventListener('click', e => {
        const btnStep = e.target.closest('.cantidad__btn');
        if (!btnStep) return;

        const wrap = btnStep.closest('.cantidad');
        const input = wrap?.querySelector('.cantidad__input');
        if (!input) return;

        const delta = btnStep.classList.contains('cantidad__btn--mas') ? 1 : -1;
        const min = parseInt(input.min, 10) || 1;
        const max = parseInt(input.max, 10) || 99;
        const nuevo = Math.min(max, Math.max(min, (parseInt(input.value, 10) || min) + delta));
        input.value = nuevo;

        // En la página del carrito el stepper persiste el cambio en el servidor
        const productoId = wrap.dataset.id;
        if (productoId) {
            postForm('/carrito/actualizar', { id: productoId, cantidad: nuevo })
                .then(data => { if (data.ok) location.reload(); })
                .catch(() => { });
        }
    });

    /* Carrito — eliminar item / vaciar (página del carrito) */
    document.addEventListener('click', e => {
        const btnElim = e.target.closest('.carrito__btn-eliminar');
        if (btnElim) {
            postForm('/carrito/eliminar', { id: btnElim.dataset.id })
                .then(data => { if (data.ok) location.reload(); })
                .catch(() => { });
        }

        const btnVaciar = e.target.closest('#btnVaciarCarrito');
        if (btnVaciar) {
            confirmarAccion('¿Vaciar todo el carrito?').then(ok => {
                if (!ok) return;
                postForm('/carrito/vaciar', {})
                    .then(data => { if (data.ok) location.reload(); })
                    .catch(() => { });
            });
        }
    });

    /* Admin — selects dependientes categoría → subcategoría */
    const selCategoria = document.getElementById('selectCategoria');
    const selSubcategoria = document.getElementById('selectSubcategoria');

    if (selCategoria && selSubcategoria && window.ADMIN_SUBCATS) {
        const filtrarSubcategorias = () => {
            const catId = parseInt(selCategoria.value, 10);
            const actual = parseInt(selSubcategoria.dataset.actual, 10) || null;
            const subs = window.ADMIN_SUBCATS.filter(s => s.categoria_id === catId);

            selSubcategoria.innerHTML = '';
            if (subs.length === 0) {
                selSubcategoria.add(new Option('— Sin subcategorías —', ''));
                return;
            }
            subs.forEach(s => {
                selSubcategoria.add(new Option(s.nombre, s.id, false, s.id === actual));
            });
        };

        selCategoria.addEventListener('change', filtrarSubcategorias);
        filtrarSubcategorias();
    }

    /* Admin — vista previa de imagen al subir */
    const inputImagen = document.getElementById('inputImagen');
    const previewImagen = document.getElementById('previewImagen');

    if (inputImagen && previewImagen) {
        inputImagen.addEventListener('change', () => {
            const archivo = inputImagen.files[0];
            if (archivo) previewImagen.src = URL.createObjectURL(archivo);
        });
    }

    /* Formularios con confirmación (eliminar, etc.) */
    document.querySelectorAll('form.js-confirm').forEach(form => {
        form.addEventListener('submit', e => {
            e.preventDefault();
            confirmarAccion(form.dataset.mensaje || '¿Confirmar esta acción?').then(ok => {
                if (ok) form.submit(); // submit() no vuelve a disparar este listener
            });
        });
    });

    /* Menú hamburguesa (móvil) */
    const hamburguesaBtn = document.getElementById('hamburguesaBtn');
    const menuMovil = document.getElementById('menuMovil');

    if (hamburguesaBtn && menuMovil) {
        hamburguesaBtn.addEventListener('click', e => {
            e.stopPropagation();
            const abierto = !menuMovil.hidden;
            menuMovil.hidden = abierto;
            hamburguesaBtn.setAttribute('aria-expanded', String(!abierto));
        });

        document.addEventListener('click', e => {
            if (!menuMovil.hidden && !menuMovil.contains(e.target)) {
                menuMovil.hidden = true;
                hamburguesaBtn.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                menuMovil.hidden = true;
                hamburguesaBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* Admin — orden manual: filas dinámicas y total estimado */
    const ordenItems = document.getElementById('ordenItems');
    const btnAgregarItem = document.getElementById('btnAgregarItem');

    if (ordenItems && btnAgregarItem) {
        const recalcularTotal = () => {
            let total = 0;
            ordenItems.querySelectorAll('.orden-items__fila').forEach(fila => {
                const opcion = fila.querySelector('select').selectedOptions[0];
                const precio = parseFloat(opcion?.dataset.precio || 0);
                const cant = parseInt(fila.querySelector('input').value, 10) || 0;
                total += precio * cant;
            });
            const el = document.getElementById('ordenTotal');
            if (el) el.textContent = 'US$ ' + total.toLocaleString('es-UY', { minimumFractionDigits: 2 });
        };

        btnAgregarItem.addEventListener('click', () => {
            const plantilla = ordenItems.querySelector('.orden-items__fila');
            const fila = plantilla.cloneNode(true);
            fila.querySelector('select').value = '';
            fila.querySelector('input').value = 1;
            ordenItems.appendChild(fila);
            recalcularTotal();
        });

        ordenItems.addEventListener('click', e => {
            const quitar = e.target.closest('.orden-items__quitar');
            if (quitar && ordenItems.querySelectorAll('.orden-items__fila').length > 1) {
                quitar.closest('.orden-items__fila').remove();
                recalcularTotal();
            }
        });

        ordenItems.addEventListener('input', recalcularTotal);
        ordenItems.addEventListener('change', recalcularTotal);
    }

    /* Checkout — formato de tarjeta y validación */
    const formCheckout = document.getElementById('formCheckout');

    if (formCheckout) {
        const iTitular = document.getElementById('nombre_pago');
        const iTarjeta = document.getElementById('numeroTarjeta');
        const eTitular = document.getElementById('errorTitular');
        const eTarjeta = document.getElementById('errorTarjeta');

        // Autoformato: "0000 0000 0000 0000"
        iTarjeta?.addEventListener('input', () => {
            const digitos = iTarjeta.value.replace(/\D/g, '').slice(0, 16);
            iTarjeta.value = digitos.replace(/(\d{4})(?=\d)/g, '$1 ');
        });

        function validarTarjeta() {
            const digitos = (iTarjeta?.value || '').replace(/\D/g, '');
            if (!digitos) { marcarError(iTarjeta, eTarjeta, 'El número de tarjeta es obligatorio'); return false; }
            if (digitos.length !== 16) { marcarError(iTarjeta, eTarjeta, 'Debe tener 16 dígitos'); return false; }
            limpiarError(iTarjeta, eTarjeta);
            return true;
        }

        iTitular?.addEventListener('blur', () => validarRequerido(iTitular, eTitular, 'El titular es obligatorio'));
        iTarjeta?.addEventListener('blur', validarTarjeta);

        formCheckout.addEventListener('submit', e => {
            const ok = [
                validarRequerido(iTitular, eTitular, 'El titular es obligatorio'),
                validarTarjeta()
            ];
            if (ok.includes(false)) e.preventDefault();
        });
    }

    /* Validación — Login */
    const formLogin = document.getElementById('formLogin');

    if (formLogin) {
        const iEmail = document.getElementById('email');
        const iPass = document.getElementById('password');
        const eEmail = document.getElementById('errorEmail');
        const ePass = document.getElementById('errorPass');

        togglePassword('togglePass', iPass);

        iEmail?.addEventListener('blur', () => validarEmailCampo(iEmail, eEmail));
        iPass?.addEventListener('blur', () => validarRequerido(iPass, ePass, 'La contraseña es obligatoria'));

        formLogin.addEventListener('submit', e => {
            const ok = [
                validarEmailCampo(iEmail, eEmail),
                validarRequerido(iPass, ePass, 'La contraseña es obligatoria')
            ];
            if (ok.includes(false)) e.preventDefault();
        });
    }

    /* Validación — Registro */
    const formRegistro = document.getElementById('formRegistro');

    if (formRegistro) {
        const iNombre = document.getElementById('nombre');
        const iApellido = document.getElementById('apellido');
        const iEmail = document.getElementById('email');
        const iPass = document.getElementById('password');
        const iPass2 = document.getElementById('password2');
        const eNombre = document.getElementById('errorNombre');
        const eApellido = document.getElementById('errorApellido');
        const eEmail = document.getElementById('errorEmail');
        const ePass = document.getElementById('errorPass');
        const ePass2 = document.getElementById('errorPass2');

        togglePassword('togglePass', iPass);
        togglePassword('togglePass2', iPass2);

        iNombre?.addEventListener('blur', () => validarRequerido(iNombre, eNombre, 'El nombre es obligatorio'));
        iApellido?.addEventListener('blur', () => validarRequerido(iApellido, eApellido, 'El apellido es obligatorio'));
        iEmail?.addEventListener('blur', () => validarEmailCampo(iEmail, eEmail));
        iPass?.addEventListener('blur', () => validarPasswordCampo(iPass, ePass));
        iPass2?.addEventListener('blur', () => validarCoincidencia(iPass2, iPass, ePass2));

        formRegistro.addEventListener('submit', e => {
            const ok = [
                validarRequerido(iNombre, eNombre, 'El nombre es obligatorio'),
                validarRequerido(iApellido, eApellido, 'El apellido es obligatorio'),
                validarEmailCampo(iEmail, eEmail),
                validarPasswordCampo(iPass, ePass),
                validarCoincidencia(iPass2, iPass, ePass2)
            ];
            if (ok.includes(false)) e.preventDefault();
        });
    }

    /* Slider de banners (home) */
    const sliderTrack = document.getElementById('sliderTrack');
    const sliderDots = document.getElementById('sliderDots');

    if (sliderTrack && sliderDots) {
        const slides = sliderTrack.querySelectorAll('.slider__slide');
        const total = slides.length;
        let current = 0;
        let timer;

        const dots = Array.from({ length: total }, (_, i) => {
            const d = document.createElement('button');
            d.className = 'slider__dot' + (i === 0 ? ' activo' : '');
            d.setAttribute('aria-label', `Ir al banner ${i + 1}`);
            d.addEventListener('click', () => goTo(i));
            sliderDots.appendChild(d);
            return d;
        });

        // Desplazamiento en píxeles: translateX(%) es inconsistente entre
        // navegadores cuando el track es más ancho que el visor (Safari).
        function goTo(n) {
            current = (n + total) % total;
            const ancho = sliderTrack.parentElement.clientWidth;
            sliderTrack.style.transform = `translateX(-${current * ancho}px)`;
            dots.forEach((d, i) => d.classList.toggle('activo', i === current));
            resetTimer();
        }

        // Recalcular al rotar el teléfono o cambiar el tamaño de la ventana
        window.addEventListener('resize', () => {
            const ancho = sliderTrack.parentElement.clientWidth;
            sliderTrack.style.transition = 'none';
            sliderTrack.style.transform = `translateX(-${current * ancho}px)`;
            sliderTrack.getBoundingClientRect();
            sliderTrack.style.transition = '';
        });

        function resetTimer() {
            clearInterval(timer);
            timer = setInterval(() => goTo(current + 1), 4500);
        }

        document.getElementById('sliderPrev')?.addEventListener('click', () => goTo(current - 1));
        document.getElementById('sliderNext')?.addEventListener('click', () => goTo(current + 1));

        resetTimer();
    }

});
