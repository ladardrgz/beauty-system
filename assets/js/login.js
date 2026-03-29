import { mostrarExito, mostrarError } from './alertas.js';

document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('loginForm');

  form.addEventListener('submit', async e => {
    e.preventDefault();

    const usuario = document.getElementById('usuario').value.trim();
    const contrasena = document.getElementById('contrasena').value.trim();

    if (!usuario || !contrasena) {
      mostrarError('Campos incompletos', 'Por favor completá todos los campos.');
      return;
    }

    try {
      const res = await fetch('/BeautySystem/index.php?controller=Login&action=loginApi', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ usuario, contrasena })
      });

      const data = await res.json();
      console.log('Respuesta del servidor:', data);

      if (data.success) {
        mostrarExito('¡Bienvenido!', data.message).then(() => {
          const destino = data.redirect || '/BeautySystem/index.php?controller=Panel&action=dashboard';
          window.location.href = destino;
        });
        return;
      }

      let titulo = 'Error de autenticación';
      let mensaje = data.message || 'Usuario o contraseña incorrectos.';
      if (mensaje.includes('inactivo')) titulo = 'Cuenta inactiva';
      else if (mensaje.includes('activada')) titulo = 'Cuenta no activada';
      else if (mensaje.includes('sesión activa')) titulo = 'Sesión existente';

      if (data.reenviar_activacion && data.id_usuario) {

        Swal.fire({
          title: titulo,
          icon: 'warning',
          html: `
            <p>${mensaje}</p>
            <a href="#" id="btnReenviarActivacion" 
               style="display:inline-block;margin-top:10px;font-weight:bold;color:#d94b8c;">
              Reenviar correo de activación
            </a>
          `,
          confirmButtonText: 'Aceptar',
          didOpen: () => {
            document.getElementById('btnReenviarActivacion').addEventListener('click', async e => {
              e.preventDefault();

              try {
                const resp = await fetch('/BeautySystem/index.php?controller=Login&action=reenviarActivacion', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id_usuario: data.id_usuario }) // 🚀 Aquí mandamos JSON correctamente
                });

                const resultado = await resp.json();
                resultado.success
                  ? Swal.fire('Correo reenviado', resultado.message, 'success')
                  : Swal.fire('Error', resultado.message || 'No se pudo reenviar el correo.', 'error');

              } catch (err) {
                Swal.fire('Error de conexión', 'No se pudo contactar con el servidor.', 'error');
              }
            });
          }
        });

      } else {
        mostrarError(titulo, mensaje);
      }

    } catch (err) {
      console.error('Error al conectar o procesar la solicitud:', err);
      mostrarError('Error del servidor', 'No se pudo conectar con el servidor. Intentalo más tarde.');
    }
  });
});
