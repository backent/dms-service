<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AWSController extends Controller
{

  public function upload(Request $request) {
    $path = $request->input('path');
    $file = $request->file('file');
    $filename = $request->input('filename');
    Storage::disk('s3')->putFileAs($path, $file, $filename, 'public');
    return 'ok';
  }

  public function move(Request $request) {
    $from = $request->input('from');
    $to = $request->input('to');
    Storage::disk('s3')->move($from, $to);
    return 'ok';
  }

  public function remove(Request $request) {
    $path = $request->input('path');
    Storage::disk('s3')->delete($path);
    return 'ok';
  }
}
