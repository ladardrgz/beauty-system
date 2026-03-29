import { mostrarExito, mostrarError } from './alertas.js';

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('formRecuperar');
  const emailInput = document.getElementById('email');
  const btnSubmit = form.querySelector('button[type="submit"]');

  // Limpia mensajes de error visuales
  const limpiarErrores = () => {
    const msg = emailInput.parentElement.querySelector('.error-msg');
    if (msg) msg.remove();
    emailInput.classList.remove('error');
  };

  // Muestra error debajo del campo
  const mostrarErrorCampo = (mensaje) => {
    limpiarErrores();
    const msg = document.createElement('small');
    msg.classList.add('error-msg');
    msg.textContent = mensaje;
    msg.style.color = 'red';
    msg.style.display = 'block';
    msg.style.marginTop = '4px';
    emailInput.classList.add('error');
    emailInput.parentElement.appendChild(msg);
  };

  // Valida formato del correo
  const validarCorreo = (correo) => {
    const regex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return regex.test(correo.trim());
  };

  // Evento submit
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    limpiarErrores();

    const correo = emailInput.value.trim();

    // Validaciones locales
    if (!correo) {
      mostrarErrorCampo('Por favor, ingresá tu correo electrónico.');
      return;
    }
    if (!validarCorreo(correo)) {
      mostrarErrorCampo('El formato del correo no es válido.');
      return;
    }

    // Bloquear botón mientras se envía
    const textoOriginal = btnSubmit.textContent;
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Enviando...';

    try {
      const res = await fetch('/MizzaStore/index.php?controller=Login&action=solicitarRecuperacion', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ correo })
      });

      const data = await res.json();

      if (data.success) {
        mostrarExito('Correo enviado', data.message);
        form.reset();
      } else {
        mostrarError('Error', data.message || 'No se pudo enviar el correo. Intenta nuevamente.');
      }
    } catch (err) {
      console.error('Error al conectar con el servidor:', err);
      mostrarError('Error de conexión', 'No se pudo contactar con el servidor. Intenta más tarde.');
    } finally {
      btnSubmit.disabled = false;
      btnSubmit.textContent = textoOriginal;
    }
  });
});
