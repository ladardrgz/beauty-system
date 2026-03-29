<?php
// ============================================================
// Archivo: core/ModuloHelper.php
// ------------------------------------------------------------
// Clase auxiliar para gestionar los módulos que cada perfil
// tiene autorizados dentro del sistema.
// 
// Usa la clase ModuloModel para consultar la base de datos
// y la clase Sesion para determinar el perfil actual.
// ============================================================

require_once 'models/ModuloModel.php';
require_once 'core/Sesion.php';

class ModuloHelper
{
    /**
     * Devuelve la lista de módulos a los que un perfil tiene acceso.
     *
     * Flujo:
     *  1️⃣ Si no se pasa un perfil explícitamente, se obtiene el perfil actual desde la sesión.
     *  2️⃣ Si la sesión no está iniciada, se inicializa como invitado.
     *  3️⃣ Se consultan los módulos asociados al perfil en la base de datos.
     *  4️⃣ Si no se encuentra ningún módulo, devuelve un array vacío.
     *
     * @param int|null $relacion_perfil ID del perfil (opcional)
     * @return array Lista de módulos autorizados
     */
    public static function obtenerModulosAutorizados(?int $relacion_perfil = null): array
    {
        // 🔹 Asegurar que haya sesión activa (si no existe, se crea como invitado)
        Sesion::inicializarInvitado();

        // 🔹 Si no se especificó el perfil, obtenerlo de la sesión actual
        if ($relacion_perfil === null) {
            $relacion_perfil = Sesion::obtenerPerfil();
        }

        // 🔹 Validar perfil
        if (empty($relacion_perfil) || !is_numeric($relacion_perfil)) {
            error_log("⚠️ Perfil no válido o indefinido en ModuloHelper.");
            return [];
        }

        // 🔹 Consultar módulos desde la base de datos
        try {
            $moduloModel = new ModuloModel();
            $modulos = $moduloModel->obtenerModulosPorPerfil($relacion_perfil);

            if (is_array($modulos) && !empty($modulos)) {
                return $modulos;
            }

            // Si no hay módulos, devolver array vacío
            return [];
        } catch (Exception $e) {
            error_log("❌ Error al obtener módulos del perfil {$relacion_perfil}: " . $e->getMessage());
            return [];
        }
    }
}
