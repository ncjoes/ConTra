<?php
require_once('vendor/autoload.php');
require_once('includes/helpers.php');
require_once('includes/processor.php');

if ($stage == 3) {
    //sort result set ($badRows) by object-attr value
    $result = [];
    foreach ($badRows as $badRow) {
        $realRow = $csv->fetchOne($badRow);
        $result[ $badRow ] = $realRow[ $objectCol ];
    }
    asort($result);
    $badRows = array_keys($result);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">

    <title>ConTra :: <?= $title; ?></title>

    <link rel="icon" href="#">
    <link rel="apple-touch-icon" href="#">

    <!-- Styles -->
    <link href="css/app.css" rel="stylesheet">
    <style type="text/css">
        body{
            font-family: Cambria, sans-serif;
        }
        .margin-top-4em{
            margin-top: 4em;
        }
    </style>

    <script src="js/app.js" type="text/javascript"></script>
    <script src="js/app.utils.js" type="text/javascript"></script>
    <script src="js/Chart.min.js" type="text/javascript"></script>

</head>
<body>
<div class="container">
    <div class="row margin-top-4em">
        <div class="col-xs-10 col-xs-offset-1 col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title text-center"><?= $title; ?></h3>
                </div>
                <div class="panel-body">
                    <form class="form-horizontal" role="form" method="post" enctype="multipart/form-data">
                        <?php
                        switch ($stage) {
                            case 1 : { ?>
                                <input type="hidden" name="stage" value="1">
                                <div class="form-group">
                                    <label class="col-sm-3" for="file">Choose File</label>
                                    <div class="col-sm-9">
                                        <input type="file" class="form-control" id="file" name="file" required accept="text/csv, .csv">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3">Config</label>
                                    <div class="col-sm-9">
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" value="" name="use_headers" checked>
                                                Use first row as column headers
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3">Analysis Mode</label>
                                    <div class="col-sm-9">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="mode" id="mode-1" value="1" checked>
                                                Multiple Attribute Values (MAV)
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="mode" id="mode-2" value="2"
                                                    <?= (isset($testMode) and $testMode == 2) ? 'checked' : ''; ?>>
                                                Single Attribute Value (SAV)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-9 col-sm-offset-3">
                                        <button class="btn btn-primary" type="submit">Upload</button>
                                    </div>
                                </div>
                                <?php
                            break;
                            }
                            case 2 :
                            case 3 : {
                                ?>
                                <input type="hidden" name="stage" value="2">
                                <input type="hidden" name="mode" value="<?= $testMode; ?>">
                                <input type="hidden" name="use_headers" value="<?= intval($use_headers) ?>">
                                <input type="hidden" name="path" value="<?= $path; ?>">
                                <div class="form-group">
                                    <label class="col-sm-3" for="object">Object Attribute</label>
                                    <div class="col-sm-9">
                                        <select name="object_col" class="form-control" required id="object">
                                            <option selected></option>
                                            <?php
                                            foreach ($headers as $index => $header) {
                                                ?>
                                                <option value="<?= $index; ?>"
                                                    <?= (!is_null($objectCol) and $objectCol == $index) ? 'selected' : '' ?>>
                                                    <?= $header; ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <?php
                                if ($testMode == 1) {
                                    ?>
                                    <fieldset>
                                        <legend class="small">
                                            Select Test Attributes and Their Mutually Exclusive Values
                                            <small class="pull-right"><span class="text-danger">*</span> unchecked Attributes are ignored!</small>
                                        </legend>
                                        <?php
                                        foreach ($headers as $index => $label) {
                                            $IN_TC = in_array($index, $TC);
                                            ?>
                                            <div class="form-group"
                                                 id="attr-<?= $index; ?>" <?= ($index == $objectCol) ? 'style="display:none;"' : '' ?> >
                                                <div class="col-sm-1">
                                                    <div class="checkbox">
                                                        <label>
                                                            <input type="checkbox" value="<?= $index; ?>"
                                                                   name="TC[<?= $index; ?>]" <?= $IN_TC ? 'checked' : '' ?>
                                                                   data-toggle="test-cols-<?= $index; ?>"/>
                                                        </label>
                                                    </div>
                                                </div>
                                                <label class="col-sm-5" for="test-cols-<?= $index; ?>"><?= $label ?></label>
                                                <div class="col-sm-6">
                                                    <select name="test_cols[<?= $index; ?>][]" class="form-control" id="test-cols-<?= $index; ?>"
                                                        <?= $IN_TC ? 'multiple required' : 'disabled'; ?>>
                                                        <?php
                                                        foreach ($colValues[ $index ] as $value) {
                                                            ?>
                                                            <option value="<?= $value; ?>"
                                                                <?= ($IN_TC and in_array($value, $testCols[ $index ])) ? 'selected' : ''; ?>>
                                                                <?= empty($value) ? 'NULL' : $value; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                    </fieldset>
                                    <?php
                                }
                                elseif ($testMode == 2) {
                                    ?>
                                    <fieldset>
                                        <legend class="small">Select Mutually Exclusive Attributes and Values</legend>
                                        <?php
                                        foreach ($headers as $index => $label) {
                                            if ($index != $objectCol) {
                                                ?>
                                                <div class="form-group" id="attr-<?= $index; ?>">
                                                    <label class="col-sm-6" for="test-cols"><?= $label ?></label>
                                                    <div class="col-sm-6">
                                                        <select name="test_cols[<?= $index; ?>]" class="form-control" id="test-cols">
                                                            <option value="-101">-ignore-</option>
                                                            <?php
                                                            foreach ($colValues[ $index ] as $value) {
                                                                ?>
                                                                <option value="<?= $value; ?>"
                                                                    <?= (isset($testCols[ $index ]) and $testCols[ $index ] == $value) ?
                                                                        'selected' : ''; ?>>
                                                                    <?= empty($value) ? 'NULL' : $value; ?>
                                                                </option>
                                                                <?php
                                                            }
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </fieldset>
                                    <?php
                                }
                                ?>
                                <div class="form-group">
                                    <div class="col-xs-12 text-center">
                                        <button class="btn btn-primary" type="submit">Analyse</button>
                                        <a class="btn btn-default" href="index.php">Reset</a>
                                    </div>
                                </div>
                                <?php
                            break;
                            }
                        }
                        ?>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    if ($stage == 3) {
        ?>
        <h2 class="page-header">Results <strong class="pull-right small">Time: <?= $compute_time; ?> seconds</strong></h2>
        <div class="row">
            <div class="col-xs-12">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th rowspan="2" width="5%">Rec.#</th>
                            <th rowspan="2">Contradictory Object(s) <a title="Identified by <?= $headers[ $objectCol ]; ?>" href="#!">?</a></th>
                            <th colspan="<?= count($testCols) ?>">Contradictory Attribute Value(s)</th>
                        </tr>
                        <tr>
                            <?php
                            $testCols = array_keys($testCols);
                            foreach ($testCols as $col) {
                                ?>
                                <th><?= $headers[ $col ] ?></th>
                                <?php
                            }
                            ?>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        foreach ($badRows as $badRow) {
                            $realRow = $csv->fetchOne($badRow)
                            ?>
                            <tr>
                                <td><?= $badRow; ?></td>
                                <td><?= $realRow[ $objectCol ]; ?></td>
                                <?php
                                foreach ($testCols as $col) {
                                    ?>
                                    <td><?= $realRow[ $col ] ?></td>
                                    <?php
                                }
                                ?>
                            </tr>
                            <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12 col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3">
                <div class="tiny-padding text-center">
                    <canvas id="chart"></canvas>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
</div>
<script type="text/javascript">
  $(function () {
    $('select#object').on('change', function (e) {
      var $val = $(this).val();
      //console.log($val);
      $('div[id*=attr-]').show();
      $('div#attr-' + $val).hide();
    });

    $('input[name*=TC]').on('change', function (e) {
      var $val = $(this).attr('data-toggle');
      var control = $('select[id=' + $val + ']');
      console.log($val);
      if ($(this).is(':checked')) {
        control.prop('disabled', false);
        control.prop('multiple', true);
        control.prop('required', true);
      }
      else {
        control.prop('multiple', false);
        control.prop('required', false);
        control.prop('disabled', true);
      }
    });
  });
</script>

<?php if ($stage == 3) { ?>
    <script type="text/javascript">
      $(function () {
        var count = <?= $rowCount; ?>;
        var badRows = <?= count($badRows); ?>;
        var goodRows = count - badRows;
        var badRowsRatio = realRound((badRows / count * 100), 4);
        var data = {
          labels: [
            badRows + " Contradicting Data (" + badRowsRatio + "%)",
            goodRows + " Non-Contradicting Data (" + (100 - badRowsRatio) + "%)"
          ],
          datasets: [
            {
              data: [badRows, goodRows],
              backgroundColor: [
                "#FF6384",
                "#36A2EB"
              ],
              hoverBackgroundColor: [
                "#FF6384",
                "#36A2EB",
              ]
            }]
        };

        // For a pie chart
        var ctx = $('canvas#chart');
        var myPieChart = new Chart(ctx, {
          type: 'pie',
          data: data,
          options: {}
        });
        $('');
      });
    </script>
<?php } ?>
</body>
</html>
