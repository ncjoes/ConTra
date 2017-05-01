<?php

use League\Csv\Reader;

//CONSTANTS
define('DS', DIRECTORY_SEPARATOR);
if (!ini_get("auto_detect_line_endings")) {
    ini_set("auto_detect_line_endings", '1');
}

//capture all POST data
$IN = $_POST;

//set defaults
$stage = isset($IN['stage']) ? $IN['stage'] : 1;
$title = 'Upload CSV File';

switch ($stage) {
    case 1: {
        if (isset($_FILES['file'])) {
            $path = uploadToRAM($_FILES['file']);
            $use_headers = isset($IN['use_headers']);

            //pre-process file
            $csv = Reader::createFromPath($path);
            $headers = csvHeaders($csv, $use_headers);
            $colValues = colValues($csv, $headers, $use_headers, true);

            //advance stage
            $stage++;
            $title = 'Choose Columns of Interest';

            //dummy
            $objectCol = null;
            $testCols = [];
            $TC = [];
            $testMode = $IN['mode'];
        }
    break;
    }
    case 2: {
        $path = $IN['path'];
        $use_headers = $IN['use_headers'] == 1;
        $csv = Reader::createFromPath($path);
        $headers = csvHeaders($csv, $use_headers);
        $colValues = colValues($csv, $headers, $use_headers, true);

        $objectCol = $IN['object_col'];
        $testCols = isset($IN['test_cols']) ? $IN['test_cols'] : [];
        $testMode = $IN['mode'];

        $TC = [];
        $badRows = [];
        $rowCount = 0;

        switch ($testMode) {
            case 1 : { //MAV;

                $TC = isset($IN['TC']) ? $IN['TC'] : [];

                if (sizeof($TC)) {
                    $rowsToCheck = [];
                    $checkedRows = [];
                    $labelToIndexMap = [];
                    $testColsCount = count($TC);
                    foreach ($csv as $index => $row) {

                        if (($index == 0 and $use_headers) or empty($row[ $objectCol ]))
                            continue;

                        $concernedColsCount = 0;
                        $subset = array_only($row, $TC);
                        foreach ($TC as $tc) {
                            if (!in_array($subset[ $tc ], $testCols[ $tc ])) {
                                break;
                            }
                            $concernedColsCount++;
                        }
                        if ($concernedColsCount == $testColsCount) {
                            $rowsToCheck[ $index ] = $row;
                        }
                    }
                    foreach ($rowsToCheck as $index => $row) {

                        $subset = array_only($row, $TC);

                        if (!isset($checkedRows[ $row[ $objectCol ] ])) {
                            $checkedRows[ $row[ $objectCol ] ] = ['rowKey' => $index, 'testValues' => $subset];
                            $labelToIndexMap[ $row[ $objectCol ] ] = $index;
                        }
                        elseif ($subset !== $checkedRows[ $row[ strtolower($objectCol) ] ]['testValues']) {
                            $badRows[] = $index;

                            if (!in_array($labelToIndexMap[ $row[ $objectCol ] ], $badRows)) {
                                $badRows[] = $labelToIndexMap[ $row[ $objectCol ] ];
                            }
                        }
                    }
                    $rowCount = count($checkedRows);
                }
            break;
            }
            case
            2 : { //SAV;
                $testCols = array_diff($testCols, ['-101']);
                foreach ($csv as $index => $row) {
                    $rowCount++;

                    if (($index == 0 and $use_headers) or empty($row[ $objectCol ]))
                        continue;

                    $subsetCols = array_only($row, array_keys($testCols));
                    //check for set equality...
                    if ($subsetCols === $testCols)
                        $badRows[] = $index;
                }
            break;
            }
        }

        //advance stage
        $stage++;
        $title = 'Choose Columns of Interest';
    break;
    }
}
