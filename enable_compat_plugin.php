<?php
/**
 * Backward Compatibility Plugin Enabler
 * One-time Joomla recovery script
 */

/* ================= PATH AND LOCK ================= */
$lockFile = __DIR__ . '/.bcpe.lock';
$lockExists = file_exists($lockFile);

/* ================= JOOMLA CONFIG ================= */
define('_JEXEC', 1);
define('JPATH_BASE', __DIR__);

if (!file_exists(JPATH_BASE . '/configuration.php')) {
    exit('Joomla configuration.php not found.');
}

require_once JPATH_BASE . '/configuration.php';
$config = new JConfig();

/* ================= DATABASE CONNECTION ================= */
$enabledPlugins = [];
$mysqli = null;

$mysqli = @new mysqli(
    $config->host,
    $config->user,
    $config->password,
    $config->db
);

if ($mysqli->connect_error) {
    $mysqli = null; // mark as failed
}

/* ================= PLUGIN AUTO-DETECTION ================= */
if (!$lockExists && $mysqli) {
    $plugins = [
        'plg_system_compat',
        'plg_behaviour_compat',
        'compat'
    ];

    $stmt = $mysqli->prepare("UPDATE `".$config->dbprefix."extensions` SET enabled = 1 WHERE element = ?");
    foreach ($plugins as $plugin) {
        $stmt->bind_param('s', $plugin);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            $result = $mysqli->query("SELECT extension_id, name FROM `".$config->dbprefix."extensions` WHERE element = '$plugin'");
            if ($row = $result->fetch_assoc()) {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
                $host = $_SERVER['HTTP_HOST'];
                $path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
                $adminLink = $protocol . $host . $path . '/administrator/index.php?option=com_plugins&view=plugins&id=' . $row['extension_id'];

                $enabledPlugins[] = [
                    'id' => $row['extension_id'],
                    'name' => $row['name'],
                    'link' => $adminLink
                ];
            }
        }
    }
    $stmt->close();
    file_put_contents($lockFile, time()); // create lock
}

/* ================= CLOSE DB ================= */
if ($mysqli) {
    $mysqli->close();
}

/* ================= DELETE SCRIPT ================= */
if (isset($_POST['delete_script'])) {
    if (file_exists($lockFile)) unlink($lockFile);
    unlink(__FILE__);
    exit('Script and lock file deleted successfully.');
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Backward Compatibility Plugin Enabler</title>
    <style>
        body { font-family: Arial; background:#0f172a; color:#e5e7eb; display:flex; align-items:center; justify-content:center; height:100vh; margin:0; }
        .card { background:#020617; padding:30px; border-radius:12px; width:90%; max-width:700px; text-align:center; }
        table { width:100%; border-collapse: collapse; margin-top:20px; }
        th, td { padding:10px; border:1px solid #444; text-align:left; }
        th { background:#1e293b; }
        button { background:#dc2626; color:#fff; padding:10px 18px; border:0; border-radius:8px; cursor:pointer; margin-top:20px; }
        a { color:#60a5fa; text-decoration:none; }
        p.notice { color:#fbbf24; font-weight:bold; margin-top:15px; }
    </style>
</head>
<body>
<div class="card">
    <h2>Backward Compatibility Plugin Enabler</h2>

    <?php 
    if ($lockExists) {
        echo '<p class="notice">This script has already been executed.</p>';
    }

    if (!empty($enabledPlugins)) {
        echo '<p>Plugins enabled: <strong>'.count($enabledPlugins).'</strong></p>';
        echo '<table>';
        echo '<tr><th>ID</th><th>Name</th><th>Admin Link</th></tr>';
        foreach ($enabledPlugins as $plugin) {
            echo '<tr>';
            echo '<td>'.$plugin['id'].'</td>';
            echo '<td>'.$plugin['name'].'</td>';
            echo '<td><a href="'.$plugin['link'].'" target="_blank">View in Admin</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    } elseif (!$lockExists) {
        echo '<p>No compatibility plugins were found to enable or DB connection failed.</p>';
    }
    ?>

    <form method="post">
        <button name="delete_script">Delete Script & Lock File</button>
    </form>
</div>
</body>
</html>
