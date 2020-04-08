<?php
function read_dir($path) {
    $dirs = glob($path);
    $files = [];
    foreach ($dirs as $k => $dir) {
        if (is_dir($dir)) {
            if ($dir != '.' && $dir != '..') {
                $files = array_merge($files, read_dir($dir . '/*'));
            }
        } else {
            $files[] = $dir;
        }
    }
    return $files;
}

function logs($msg) {
    static $i;
    $i++;
    $msg = iconv('utf-8', 'gb2312', $msg);
    echo "[$i]" . $msg . PHP_EOL;
}

logs('开始运行...');
$env = pathinfo(__FILE__);
$env_dir = str_replace('\\', '/', $env['dirname']);

$base = $env_dir . '/app';
$base = iconv('utf-8', 'gb2312', $base);
$dir = $base . '/*';
$files = read_dir($dir);
$widget = [];
$keep_time = strtotime('-24 hours');
foreach ($files as $key => $file) {
    $mtime = filemtime($file);
    if ($mtime >= $keep_time) {
        $widget[] = $file;
    }
}
logs('扫描完毕...');

//删除目录下所有文件和子目录
function deldir($directory) {
    if (is_dir($directory)) {
        if ($dir_handle = @opendir($directory)) {
            while (false !== ($filename = readdir($dir_handle))) {
                $file = $directory . '/' . $filename;
                if ($filename != '.' && $filename != '..') {
                    if (is_dir($file)) {
                        deldir($file);
                    } else {
                        unlink($file);
                    }
                }
            }
            closedir($dir_handle);

        }
        rmdir($directory);
    }
}

$widget_dir = $env_dir . '/widget';
deldir($widget_dir);
logs('重建widget...');

foreach ($widget as $key => $file) {
    $info = pathinfo($file);
    if ($info['basename'] == 'config.xml') {
        continue;
    }
    $new_file = $widget_dir . str_replace($base, '', $file);
    $new_info = pathinfo($new_file);
    if (!is_dir($new_info['dirname'])) {
        mkdir($new_info['dirname'], 0777, true);
    }
    copy($file, $new_file);
}

logs('拷贝完毕...');

function addFileToZip($path, $zip) {
    global $widget_dir;
    $handler = opendir($path);
    while (($filename = readdir($handler)) !== false) {
        if ($filename != '.' && $filename != '..') {
            if (is_dir($path . '/' . $filename)) {
                addFileToZip($path . '/' . $filename, $zip);
            } else {
                $file = $path . '/' . $filename;
                $zip->addFile($file, str_replace($widget_dir, 'widget', $file));
            }
        }
    }
    @closedir($path);
}
 
$zip = new ZipArchive();
$zip_name = $widget_dir . '.zip';
@unlink($zip_name);

if ($zip->open($zip_name, ZipArchive::CREATE) === true) {
    addFileToZip($widget_dir, $zip);
    $zip->close();
    logs('压缩完毕...');
} else {
    logs('压缩失败...');
}

deldir($widget_dir);