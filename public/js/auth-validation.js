document.addEventListener("DOMContentLoaded", function () {
    
    // 1. Mostrar/Ocultar Contraseñas
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Función auxiliar para pintar Verde/Rojo con Feedback de Bootstrap
    const setValidation = (input, isValid, mensajeError = '') => {
        let feedback = input.nextElementSibling;
        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = input.parentElement.nextElementSibling;
        }

        if (isValid) {
            input.classList.remove('is-invalid');
            input.classList.add('is-valid');
            if (input.parentElement.classList.contains('input-group')) {
                input.parentElement.querySelector('.input-group-text').classList.add('is-valid-addon');
            }
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.innerText = '';
                feedback.style.display = 'none';
            }
        } else {
            input.classList.remove('is-valid');
            input.classList.add('is-invalid');
            if (input.parentElement.classList.contains('input-group')) {
                input.parentElement.querySelector('.input-group-text').classList.remove('is-valid-addon');
            }
            if (feedback && feedback.classList.contains('invalid-feedback')) {
                feedback.innerText = mensajeError;
                feedback.style.display = 'block';
            }
        }
        return isValid;
    };

    // 2A. Nombres (Máximo 2 nombres, 1 espacio)
    document.querySelectorAll('.valida-nombre').forEach(input => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
            this.value = this.value.replace(/^\s+/g, '').replace(/\s{2,}/g, ' ');
            const partes = this.value.trim().split(' ');
            if (partes.length > 2) {
                this.value = partes[0] + ' ' + partes[1];
            }
            if (this.value.trim().length < 2) {
                setValidation(this, false, 'Debe tener al menos 2 letras.');
            } else {
                setValidation(this, true);
            }
        });
    });

    // 2B. Apellidos (UN SOLO APELLIDO, CERO espacios)
    document.querySelectorAll('.valida-apellido').forEach(input => {
        input.addEventListener('input', function () {
            this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ]/g, '');
            if (this.id === 'apellido_materno' && this.value === '') {
                this.classList.remove('is-invalid', 'is-valid');
                if(this.nextElementSibling) this.nextElementSibling.style.display='none';
                return;
            }
            if (this.value.length < 2) {
                setValidation(this, false, 'Mínimo 2 letras (Sin espacios).');
            } else {
                setValidation(this, true);
            }
        });
    });

    // 3. Correo Electrónico
    const inputCorreo = document.querySelector('.val-correo') || document.getElementById('correo');
    if (inputCorreo) {
        inputCorreo.addEventListener('input', function () {
            this.value = this.value.replace(/\s/g, '');
            const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (regexCorreo.test(this.value)) {
                setValidation(this, true);
            } else {
                setValidation(this, false, 'Ingrese un correo válido.');
            }
        });
    }

    // 4. Celular Boliviano
    const inputCelular = document.querySelector('.val-celular') || document.getElementById('celular');
    if (inputCelular) {
        inputCelular.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 8);
            if (this.value.length === 8 && /^[67]/.test(this.value)) {
                setValidation(this, true);
            } else {
                setValidation(this, false, 'Debe tener 8 dígitos y empezar con 6 o 7.');
            }
        });
    }

    // 5. Validación de Documento (CI o NIT)
    const tipoDoc = document.getElementById('tipo_doc');
    const numDoc = document.getElementById('numero_documento');
    if (numDoc && tipoDoc) {
        const validarDocumento = () => {
            let valor = numDoc.value.trim().toUpperCase();
            if (tipoDoc.value === 'CI') {
                numDoc.value = valor.replace(/[^0-9A-Z\-]/g, ''); 
                if (numDoc.value.length >= 5 && numDoc.value.length <= 12) {
                    setValidation(numDoc, true);
                } else {
                    setValidation(numDoc, false, 'Entre 5 y 12 caracteres.');
                }
            } else if (tipoDoc.value === 'NIT') {
                numDoc.value = valor.replace(/\D/g, ''); 
                if (numDoc.value.length >= 7 && numDoc.value.length <= 15) {
                    setValidation(numDoc, true);
                } else {
                    setValidation(numDoc, false, 'Entre 7 y 15 números.');
                }
            }
        };
        tipoDoc.addEventListener('change', validarDocumento);
        numDoc.addEventListener('input', validarDocumento);
    }

    // 6. SOLUCIÓN: Años de Experiencia Estricto
    const inputExp = document.getElementById('experiencia_anios');
    if (inputExp) {
        // Bloquear teclas no numéricas desde el teclado (como -, +, e, .)
        inputExp.addEventListener('keydown', function(e) {
            if (['e', 'E', '+', '-', '.', ','].includes(e.key)) {
                e.preventDefault();
            }
        });

        inputExp.addEventListener('input', function () {
            // Eliminar cualquier cosa que no sea dígito
            this.value = this.value.replace(/\D/g, ''); 
            
            // Si el campo se queda vacío
            if (this.value === '') {
                setValidation(this, false, 'Ingrese un valor (0 si es nuevo).');
                return;
            }

            const anios = parseInt(this.value);
            
            // Limitar a 50 años como máximo
            if (anios < 0 || anios > 50) {
                setValidation(this, false, 'Máximo 50 años permitidos.');
                // Forzamos visualmente que no pase de 50
                if(anios > 50) this.value = '50';
            } else {
                setValidation(this, true);
            }
        });
    }

    // 7. Seguridad de Contraseña
    const pass = document.getElementById('password');
    const passConfirm = document.getElementById('password_confirm');
    if (pass) {
        pass.addEventListener('input', function () {
            const val = this.value;
            let errores = [];

            if (val.includes(' ')) {
                this.value = val.replace(/\s/g, '');
                errores.push('Sin espacios.');
            }
            if (this.value.length < 8) errores.push('Mín. 8 caracteres.');
            if (!/[A-Z]/.test(this.value)) errores.push('Falta 1 mayúscula.');
            if (!/[0-9]/.test(this.value)) errores.push('Falta 1 número.');
            if (!/[@$!%*?&\-_\.,]/.test(this.value)) errores.push('Falta 1 símbolo.');

            if (errores.length === 0) {
                setValidation(this, true);
            } else {
                setValidation(this, false, errores.join(' '));
            }
            if (passConfirm) passConfirm.dispatchEvent(new Event('input')); 
        });
    }

    if (passConfirm) {
        passConfirm.addEventListener('input', function () {
            if (this.value === pass.value && this.value !== '') {
                setValidation(this, true);
            } else {
                setValidation(this, false, 'Las contraseñas no coinciden.');
            }
        });
    }

    // 8. Selectores, Direcciones y Textareas generales
    document.querySelectorAll('.val-select, .val-direccion, textarea[required]').forEach(input => {
        const evento = input.tagName === 'SELECT' ? 'change' : 'input';
        input.addEventListener(evento, function () {
            if (this.value.trim() !== '') {
                if (this.hasAttribute('minlength') && this.value.length < this.getAttribute('minlength')) {
                    setValidation(this, false, 'Mínimo ' + this.getAttribute('minlength') + ' caracteres.');
                } else {
                    setValidation(this, true);
                }
            } else {
                setValidation(this, false, 'Este campo es obligatorio.');
            }
        });
    });

    // 9. Mostrar campo "OTRO" (Especialidad)
    const categoriaSelect = document.getElementById('id_categoria');
    const inputOtraEsp = document.getElementById('otra_especialidad');
    if (categoriaSelect && inputOtraEsp) {
        const wrapperOtraEsp = document.getElementById('wrapper-otra-especialidad');
        categoriaSelect.addEventListener('change', function () {
            if (this.value === 'OTRO') {
                wrapperOtraEsp.classList.remove('d-none');
                inputOtraEsp.setAttribute('required', 'required');
            } else {
                wrapperOtraEsp.classList.add('d-none');
                inputOtraEsp.removeAttribute('required');
                inputOtraEsp.value = '';
                setValidation(inputOtraEsp, true); // Reset validation
            }
        });
    }

    // 10. Validación en el Submit
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            let formValido = true;
            
            // Disparar input a todos para que validen
            this.querySelectorAll('input[required], select[required], textarea[required]').forEach(input => {
                input.dispatchEvent(new Event(input.tagName === 'SELECT' ? 'change' : 'input'));
                if (!input.classList.contains('is-valid') && input.id !== 'apellido_materno') {
                    formValido = false;
                }
            });

            // Validar archivos
            this.querySelectorAll('.file-doc').forEach(file => {
                if (file.files.length === 0 && file.hasAttribute('required')) {
                    setValidation(file, false, 'Debe subir este documento.');
                    formValido = false;
                } else if (file.files.length > 0) {
                    setValidation(file, true);
                }
            });

            const invalidos = this.querySelectorAll('.is-invalid');
            if (!formValido || invalidos.length > 0 || !this.checkValidity()) {
                e.preventDefault(); 
                e.stopPropagation();
                const primerError = this.querySelector('.is-invalid');
                if (primerError) primerError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    });
});