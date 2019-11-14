<?php

namespace Testify;

class Psr4AutoloaderClass {

    protected $prefixes = array();

    public function register() {
        spl_autoload_register(array($this, 'loadClass'));
    }

    public function addNamespace($prefix, $base_dir, $prepend = false) {
        $prefix = trim($prefix, '\\') . '\\';
        $base_dir = rtrim(strtolower($base_dir), DIRECTORY_SEPARATOR) . '/';
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }
        if ($prepend)
            array_unshift($this->prefixes[$prefix], $base_dir);
        else
            array_push($this->prefixes[$prefix], $base_dir);
    }

    public function loadClass($class) {
        $prefix = $class;

        while (false !== $pos = strrpos($prefix, '\\')) {
            $prefix = substr($class, 0, $pos + 1);
            $relative_class = substr($class, $pos + 1);
            $mapped_file = $this->loadMappedFile($prefix, $relative_class);

            if ($mapped_file)
                return $mapped_file;

            $prefix = rtrim($prefix, '\\');
        }
        return false;
    }

    protected function loadMappedFile($prefix, $relative_class) {
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        foreach ($this->prefixes[$prefix] as $base_dir) {
            $file = $base_dir
                  . str_replace('\\', '/', $relative_class)
                  . '.php';

            if ($this->requireFile($file))
                return $file;
        }

        return false;
    }

    protected function requireFile($file) {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
        if (file_exists($path . $file)) {
            require_once $path . $file;
            return true;
        }
        return false;
    }
}

$loader = new Psr4AutoloaderClass();

$loader->register();

$loader->addNamespace('Testify', 'src/');
$loader->addNamespace('Testify\Model', 'src/models/');
$loader->addNamespace('Testify\Component', 'app/components/');
$loader->addNamespace('Testify\Router', 'app/router/');
$loader->addNamespace('Testify\Tests', 'tests/');
