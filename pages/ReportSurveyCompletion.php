<?php
namespace Stanford\ProjTRACPain;
/** @var \Stanford\ProjTRACPain\ProjTRACPain $module */

use REDCap;
require_once APP_PATH_DOCROOT . 'ProjectGeneral/header.php';


if (!isset($module)) {
    $module = \ExternalModules\ExternalModules::getModuleInstance('proj_trac_pain');
}

$module->emDebug("Starting Proj TRAC Pain Report");
// Include REDCap ExternalModules framework
//require_once dirname(__FILE__, 3) . '/redcap_v' . substr(REDCAP_VERSION, 0, strpos(REDCAP_VERSION, '.', 1)) . '/ExternalModules/ExternalModules.php';
// Include module class
//require_once dirname(__FILE__, 2) . '/ProjTRACPain.php';

// Get module instance
//$module = \ExternalModules\ExternalModules::getModuleInstance('ProjTRACPain');

// Call the reportSurveyCompletion method
$results = $module->reportSurveyCompletion();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Report Survey Completion</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background: #f4f4f4;
        }
    </style>
</head>
<body>
    <h2>Survey Completion Report</h2>
    <?php if (!empty($results) && is_array($results)) { ?>
        <table>
            <thead>
                <tr>
                    <?php foreach (array_keys($results[0]) as $header) { ?>
                        <th><?php echo htmlspecialchars($header); ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $row) { ?>
                    <tr>
                        <?php foreach ($row as $header => $cell) { ?>
                            <td>
                                <?php if ($header === 'record_id' && !empty($cell)) {
                                    $pid = defined('PROJECT_ID') ? PROJECT_ID : (isset($_GET['pid']) ? $_GET['pid'] : '');
                                    $url = APP_PATH_WEBROOT . "DataEntry/record_home.php?pid=$pid&arm=1&id=" . urlencode($cell);
                                    echo "<a href='" . htmlspecialchars($url) . "' target='_blank'>" . htmlspecialchars($cell) . "</a>";
                                } else {
                                    echo htmlspecialchars($cell);
                                } ?>
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No survey completion data available.</p>
    <?php } ?>
</body>
</html>

