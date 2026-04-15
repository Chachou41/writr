<?php
/**
 * Sidebar Tree Navigation for Writr template
 * Adapted from desert3 template's sidebar.php
 *
 * Displays a namespace tree synchronized with the current page.
 */

if (!defined('DOKU_INC')) die();

require_once(DOKU_INC . 'inc/search.php');

/**
 * Render the namespace tree as nested <ul>
 */
function tpl_nstree() {
    global $ID, $conf;

    $ns = getNS($ID);
    $ns = utf8_encodeFN(str_replace(':', '/', $ns));

    $data = array();
    search($data, $conf['datadir'], 'search_index', array('ns' => $ns));

    if (empty($data)) return;

    $currentLevel = 1;
    echo '<ul class="nstree">' . "\n";

    for ($i = 0; $i < count($data); $i++) {
        $item = $data[$i];

        // ACL check
        if ($item['type'] === 'd') {
            $perm = auth_quickaclcheck($item['id'] . ':*');
        } else {
            $perm = auth_quickaclcheck($item['id']);
        }
        if ($perm < AUTH_READ) continue;

        // Link name
        $linkName = noNS($item['id']);
        if ($conf['useheading'] && $item['type'] === 'f') {
            $heading = p_get_first_heading($item['id']);
            if ($heading) $linkName = $heading;
        }
        $linkName = str_replace('_', ' ', $linkName);

        // Close deeper levels
        if ($currentLevel > $item['level']) {
            echo str_repeat("</ul></li>\n", $currentLevel - $item['level']);
            $currentLevel = $item['level'];
        }

        // Render item
        if ($item['type'] === 'd') {
            $class = $item['open'] ? 'nstree__dir nstree__dir--open' : 'nstree__dir';
            echo '<li class="' . $class . '">';
            $url = wl($item['id'] . ':' . $conf['start']);
            echo '<a href="' . $url . '">' . hsc($linkName) . '</a>';
        } else {
            $isCurrent = ($item['id'] === $ID);
            $class = 'nstree__file' . ($isCurrent ? ' nstree__file--active' : '');
            echo '<li class="' . $class . '">';
            echo '<a href="' . wl($item['id']) . '">' . hsc($linkName) . '</a>';
        }

        // Handle nesting
        $nextLevel = isset($data[$i + 1]) ? $data[$i + 1]['level'] : 1;
        if ($nextLevel > $currentLevel) {
            echo '<ul>' . "\n";
        } elseif ($nextLevel < $currentLevel) {
            echo '</li>' . "\n";
            echo str_repeat("</ul></li>\n", $currentLevel - $nextLevel);
        } else {
            echo '</li>' . "\n";
        }
        $currentLevel = $nextLevel;
    }

    echo '</ul>' . "\n";
}
