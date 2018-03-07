<?php

if ( ! function_exists('resource_path'))
{
    /**
     * Get the path to the resources folder.
     *
     * @param  string $path
     * @return string
     */
    function resource_path($path = '')
    {
        return \Illuminate\Support\Facades\App::basePath('resources' . ($path ? DIRECTORY_SEPARATOR . $path : $path));
    }
}