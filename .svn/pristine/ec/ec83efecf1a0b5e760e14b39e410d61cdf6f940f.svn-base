<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Str;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public $searchedFile = null;

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $flag = config('app.autocorrect_module');
        if ($flag && false) {
            $msg  = $exception->getMessage();
            $file = $exception->getFile();
            if (Str::containsAll($msg, ['Class', 'not', 'found'])) {
                preg_match("~'(.*?)'~", $msg, $model);
                $pieces        = explode('\\', $model[1]);
                $last_word     = end($pieces);
                $path          = explode('\\', $file);
                $mdl           = array_search('Modules', $path);
                $checkRequests = array_search('Requests', $pieces);
                $checkModels = array_search('Models', $pieces);
                $this->search_file(base_path() . '/Modules/', $last_word . '.php');
                if ($this->searchedFile != null) {
                    if ($mdl) {

                        $module  = $path[$mdl + 1];
                        $replace = '';
                        //dd($msg,$module,$pieces);
                        if ($checkRequests) {
                            $replace = 'Modules\\' . $module . '\Http\Requests\\' . $last_word . '; //' . $model[1];
                        } elseif($checkModels) {
                            $replace = 'Modules\\' . $module . '\Entities\\' . $last_word . '; //' . $model[1];
                        }

                        $contents = file_get_contents($file);
                        if ($contents === false) {
                            dd('error couldnot access file. Change namespace manually', $model[1]);
                        }

                        $contents = str_replace($model[1], $replace, $contents);

                        $bytes_written = file_put_contents($file, $contents);
                        if ($bytes_written !== strlen($contents)) {
                            dd('error couldnot write file. Change namespace manually', $model[1]);
                        }

                        dd('replaced :' . $model[1] . ' with :' . $replace . '  Please reload!', 'if same message is recurring please change the namespace manually', 'or Check if the file exists in the modules  or specified directory', 'file:' . $file);
                    }
                }

            }

            if (Str::containsAll($msg, ['Target', 'class', 'does', 'not', 'exist'])) {
                $msg = str_replace('[', "'", $msg);
                $msg = str_replace(']', "'", $msg);
                preg_match("~'(.*?)'~", $msg, $model);
                $pieces = explode('\\', $model[1]);
                $mdl    = array_pop($pieces);
                $msg    = implode('\\', $pieces) . ';';

                $this->search_file(base_path() . '/Modules/', $mdl . '.php');
                if ($this->searchedFile != null) {
                    $file     = $this->searchedFile;
                    $contents = file_get_contents($file);
                    if ($contents === false) {
                        dd('error couldnot access file. Change namespace manually', $model[1]);
                    }
                    $np = explode('\\', $file);
                    $ck  = array_search('Modules', $np);
                    array_pop($np);
                    $np = array_slice($np,$ck); 
                    $msg=implode('\\',$np);

                    $contents = str_replace('namespace', 'namespace ' . $msg . '; //', $contents);

                    $bytes_written = file_put_contents($file, $contents);
                    if ($bytes_written !== strlen($contents)) {
                        dd('error couldnot write file. Change namespace manually', $model[1]);
                    }

                    dd('replaced namespace at top of the file with:' . $msg . '  Please reload!', 'if same message is recurring please change the namespace manually', 'or Check if the file exists in the modules  or specified directory', 'file: ' . $file);

                }

            }
        }
        return parent::render($request, $exception);
    }

    public function search_file($dir, $file_to_search)
    {

        $files = scandir($dir);

        foreach ($files as $key => $value) {

            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);

            if (!is_dir($path)) {

                if ($file_to_search == $value) {
                    $this->searchedFile = $path;
                    break;
                }

            } else if ($value != "." && $value != "..") {

                $this->search_file($path, $file_to_search);

            }
        }
    }

}
