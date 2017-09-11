<html>
<head>
    <base href="<?= Winged::$protocol ?>winged/"/>
    <link type="text/css" rel="stylesheet" href="./assets/css/reset.css"/>
    <link type="text/css" rel="stylesheet" href="./assets/css/font-awesome.min.css"/>
    <link type="text/css" rel="stylesheet" href="./assets/css/install.css"/>
    <meta name="viewport" content="width=device-width,user-scalable=0,initial-scale=1"/>
    <link rel="icon" href="./assets/img/fav.png"/>
    <title>Model creator</title>
    <script type="text/javascript">var URL = "<?= Winged::$protocol ?>";</script>
</head>
<body>
<?php

if (!WingedConfig::$DBEXT) {
    ?>
    <div class="bg"></div>
    <div class="black"></div>
    <div class="content">
        <div class="middle full">
            <div class="wings"></div>
            <div class="forms">
                <div id="lg" class="form active">
                    <div class="text"> Sorry, the DBEXT option are disabled in config.php</div>
                </div>
            </div>
        </div>
    </div>
    <?php
    exit;
}

$models = './models/';
$created = array();
$nmodel = false;
if (is_post() && postset('tables') && count(post('tables')) > 0 && is_dev()) {
    if (!file_exists($models)) {
        $created[] = 'Models folder do not exists.';
        $nmodel = true;
    }
    $tables = post('tables');
    if (!$nmodel) {
        foreach ($tables as $key => $t) {
            $models = './models/';
            $table = CurrentDB::sp(Database::SP_DESC_TABLE, ['table_name' => $t]);
            $class_name = formatName($t);

            $begin_class = '<?php
            class ' . $class_name . ' extends Model
{
    public function __construct(){
        parent::__construct();
        return $this;
    }';

            $table_name = 'public static function tableName()
    {
        return "' . $t . '";
    }
    
    ';

            $pk_name = getPrimaryKeyName($table);

            $pk = 'public static function primaryKeyName()
    {        
        return "' . $pk_name . '";
    }
    
    ';

            $rules = 'public function rules()
    {        
        return [];
    }
    
    ';

            $pk_value = 'public function primaryKey($pk = false)
    {        
        if($pk && (is_int($pk) || intval($pk) != 0)){
            $this->' . $pk_name . ' = $pk;
            return $this;        
        }        
        return $this->' . $pk_name . ';    
    }

    ';

            $behaviors = 'public function behaviors()
    {
        return [];
    }
    
    ';

            $reverseBehaviors = 'public function reverseBehaviors()
    {        
        return [];    
    }
    
    ';

            $labels = 'public function labels()
    {        
        return [];    
    }
    
    ';

            $messages = 'public function messages()
    {
        return [];
    }
    
    ';

            $vars = '';
            foreach ($table as $key => $field) {
                $vars .= '/** @var $' . $key . ' ' . getFieldType($field['type']) . ' */    ';
                $vars .= 'public $' . $key . ';    ';
            }
            $end_class = '}';
            $full = $begin_class . $vars . $table_name . $pk . $pk_value . $behaviors . $reverseBehaviors . $rules . $messages . $labels . $end_class;
            if (!file_exists($models . $class_name . '.php')) {
                $handle = fopen($models . $class_name . '.php', 'w+');
                fwrite($handle, $full);
                fclose($handle);
                $created[] = 'File: ' . $models . $class_name . '.php are created.';
            } else {
                $created[] = 'File: can' . "'" . 't create file ' . $models . $class_name . '.php.';
            }
        }
    }
} ?>
<div class="bg"></div>
<div class="black"></div>
<div class="content">
    <div class="middle full">
        <div class="wings"></div> <?php if (is_dev()) {
            $tables = CurrentDB::sp(Database::SP_SHOW_TABLES);
            if ($tables) { ?>
                <div class="center creator">
                    <form method="post" action="creator/">
                        <table>
                            <thead>
                            <tr>
                                <td>Original table name</td>
                                <td>Target model name</td>
                                <td> Able to create <input type="checkbox" name="checkall" id="checkall" value="false"/>
                                </td>
                            </tr>
                            </thead>
                            <tbody>                            <?php foreach ($tables as $key => $table) { ?>
                                <tr>
                                    <td><?= $table ?></td>
                                    <td><?= formatName($table) ?></td>
                                    <td><input type="checkbox" name="tables[]"
                                               value="<?= $table ?>" <?= (file_exists($models . formatName($table) . '.php')) ? 'disabled' : '' ?>/>
                                    </td>
                                </tr>                                <?php } ?>                            </tbody>
                        </table>
                        <button type="submit">Create</button>
                    </form>
                    <div class="center creator">
                        <div class="warns">                            <?php foreach ($created as $key => $txt) {
                                echobr($txt);
                            }
                            if (empty($created)) {
                                echo 'No effect on last action.';
                            } ?>                        </div>
                    </div>
                </div>                <?php } else { ?>
                <div class="forms">
                    <div id="lg" class="form active">
                        <div class="text"> Sorry, no tables found in this Database</div>
                    </div>
                </div>                <?php }
        } else { ?>
            <div class="forms">
                <div id="lg" class="form active">
                    <div class="text"> Sorry, the DEV option are disabled in config.php</div>
                </div>
            </div>            <?php } ?>    </div>
</div>
<?php function formatName($name)
{
    $name = explode('-', str_replace(array('-', '_',), '-', $name));
    $new_name = '';
    foreach ($name as $key => $splited) {
        $new_name .= ucfirst($splited);
    }
    return $new_name;
}

function getPrimaryKeyName($table)
{
    foreach ($table as $key => $field) {
        if ($field['key'] == 'PRI') {
            return $key;
        }
    }
    return false;
}

function getFieldType($str)
{
    $types = array('int' => 'integer', 'bigint' => 'integer', 'tinyint' => 'integer', 'boolean' => 'boolean', 'blob' => 'string', 'longblob' => 'string', 'text' => 'string', 'timestampe' => 'string', 'date' => 'string', 'varchar' => 'string', 'time' => 'string', 'longtext' => 'string',);
    foreach ($types as $key => $type) {
        if (gettype(stripos($str, $key)) === 'integer') {
            return $type;
        }
    }
    return 'string';
} ?></body>
<script src="./assets/js/jquery.js"></script>
<script>    $(function () {
        $('#checkall').on('change', function () {
            var $this = $(this);
            $this.closest('table').find('tbody').find('input[type=checkbox]').trigger('click');
        });
    });</script>
</html><?php