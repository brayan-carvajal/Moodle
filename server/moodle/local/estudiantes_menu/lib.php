<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin local para agregar menú de Estudiantes al navbar
 *
 * @package    local_estudiantes_menu
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extiende la navegación global del sitio
 * Esta función es llamada automáticamente por Moodle
 *
 * @param global_navigation $navigation Instancia de navegación global
 */
function local_estudiantes_menu_extend_navigation(global_navigation $navigation) {
    global $USER, $CFG;
    
    // Mostrar solo a usuarios logueados (no invitados)
    if (!isloggedin() || isguestuser()) {
        return;
    }
    
    // Obtener el nodo primario donde se agregará el menú
    $primary = $navigation->get('primary');
    
    if (!$primary) {
        return;
    }
    
    // Agregar nodo principal "Estudiantes"
    // Usamos TYPE_CONTAINER para que sea un menú desplegable
    $estudiantes = $primary->add(
        'Estudiantes',                                    // Texto a mostrar
        new moodle_url('/user/index.php', ['id' => 5]), // URL por defecto
        navigation_node::TYPE_CONTAINER,                 // Tipo: contenedor con hijos
        null,                                            // Texto personalizado
        'local-estudiantes-menu'                         // ID único (para CSS/JS)
    );
    
    // Subitem 1: Ver todos los estudiantes
    $estudiantes->add(
        'Ver todos los estudiantes',
        new moodle_url('/user/index.php', ['id' => 5]),
        navigation_node::TYPE_SETTING,
        null,
        'local-estudiantes-view'
    );
    
    // Subitem 2: Matricular (solo usuarios con permiso)
    if (has_capability('moodle/course:enrolreview', context_system::instance())) {
        $estudiantes->add(
            'Matricular estudiantes',
            new moodle_url('/enrol/users.php'),
            navigation_node::TYPE_SETTING,
            null,
            'local-estudiantes-enrol'
        );
    }
    
    // Subitem 3: Importar (solo admins)
    if (is_siteadmin()) {
        $estudiantes->add(
            'Importar desde CSV',
            new moodle_url('/admin/tool/uploaduser/index.php'),
            navigation_node::TYPE_SETTING,
            null,
            'local-estudiantes-import'
        );
    }
    
    // Subitem 4: Usuarios en línea
    $estudiantes->add(
        'Usuarios en línea',
        new moodle_url('/admin/user/online_users.php'),
        navigation_node::TYPE_SETTING,
        null,
        'local-estudiantes-online'
    );
    
    // Separador visual
    $estudiantes->add_divider();
    
    // Subitem 5: Gestionar grupos
    $estudiantes->add(
        'Gestionar grupos',
        new moodle_url('/group/index.php'),
        navigation_node::TYPE_SETTING,
        null,
        'local-estudiantes-groups'
    );
    
    // Opcional: Agregar icono (requiere icono en el tema)
    // $estudiantes->icon = new pix_icon('i/users', 'Estudiantes');
    
    // Marcar como activo si estamos en una de las páginas de estudiantes
    $currenturl = $CFG->wwwroot . $_SERVER['REQUEST_URI'];
    if (strpos($currenturl, '/user/index.php') !== false || 
        strpos($currenturl, '/enrol/users.php') !== false ||
        strpos($currenturl, '/admin/user/') !== false) {
        $estudiantes->make_active();
    }
}

/**
 * Extiende la navegación de configuración (menú de engranaje)
 *
 * @param settings_navigation $navigation
 * @param context $context
 */
function local_estudiantes_menu_extend_settings_navigation(settings_navigation $navigation, $context) {
    // Agregar opción al menú de configuración
    if (has_capability('moodle/user:create', $context)) {
        $navigation->add(
            'Configuración de menú Estudiantes',
            new moodle_url('/local/estudiantes_menu/settings.php'),
            navigation_node::TYPE_SETTING,
            null,
            'local-estudiantes-settings'
        );
    }
}
