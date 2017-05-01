<?php

function uploadToRAM(array $FH)
{
    $DEST = 'RAM'.DS.uniqid().'.csv';
    if (move_uploaded_file($FH['tmp_name'], $DEST)) {
        return $DEST;
    }

    throw new \Exception("Can not upload file");
}

function makeGenericHeaders($colCount)
{
    $headers = [];
    for ($i = 0; $i < $colCount; ++$i) {
        $headers[ (string)$i ] = 'Column-'.$i;
    }

    return $headers;
}

/**
 * @param \League\Csv\AbstractCsv $csv
 * @param bool $useFirstRow
 *
 * @return array
 */
function csvHeaders($csv, $useFirstRow = true)
{
    $headers = $csv->fetchOne();
    if (!$useFirstRow) {
        $headers = makeGenericHeaders(count($headers));
    }

    return $headers;
}

function colValues($csv, $headers, $useFirstRow = true, $unique = false)
{
    $result = [];
    foreach ($headers as $index => $label) {
        $result[ $index ] = iterator_to_array($csv->fetchColumn($index));
        if ($useFirstRow) {
            unset($result[ $index ][0]);
        }
        if ($unique) {
            $result[ $index ] = array_unique($result[ $index ]);
        }
    }

    return $result;
}
