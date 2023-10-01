<?php

namespace App\Http\Controllers;

define('Mb', 1024 * 1024);
define('limitSize', 3);
trait ApiResponseTrait{
    public function apiResponse($data=null, $message=null, $statusCode=null){
        $array = [
            'data'=> $data,
            'message'=> $message,
            'statusCode'=> $statusCode
        ];
        return response($array, $statusCode);
    }




    /************************** Function not used **************************/
    public function validImg($image, $isMulti = false, $num = 1)
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = $image->getClientOriginalExtension();
        if($isMulti){
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                return $this->apiResponse(null, "Invalid image:#". $num ." format " . $extension . ". Allowed formats: jpg, jpeg, png, gif", 400);
            }

            if (round($image->getSize(), 2) / Mb > limitSize) {
                return $this->apiResponse(null, "Image:#" . $num . " size exceeds " . limitSize . " MB limit", 400);
            }
        } else{
            if (!in_array(strtolower($extension), $allowedExtensions)) {
                return $this->apiResponse(null, "Invalid image format " . $extension . ". Allowed formats: jpg, jpeg, png, gif", 400);
            }

            if (round($image->getSize(), 2) / Mb > limitSize) {
                return $this->apiResponse(null, "Image size exceeds " . limitSize . " MB limit", 400);
            }
        }
        return null;
    }
}
