<?php

namespace Pet;

abstract class Controller
{
  public function  saveFile($name, $path): bool
  {
    return move_uploaded_file(files($name)['tmp_name'], $path);
  }
}
