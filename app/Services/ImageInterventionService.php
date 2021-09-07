<?php


namespace App\Services;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class ImageInterventionService
{
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function store($directoryName, $data = null, $imageEncode = 100, $thumbEncode = 60)
    {

        return $this->imageIntervention('store', $data, $directoryName);
    }

    public function update($oldFile, $directoryName = "categories", $imageEncode = 100, $thumbEncode = 60)
    {

        return $this->imageIntervention('update', $oldFile, $directoryName);
    }

    private function imageIntervention($type = 'store', $data = null, $directoryName = "categories", $imageEncode = 100, $thumbEncode = 60)
    {
        if ($this->request->has('image')) {

            $file = $this->request->file('image');
            $filename = $this->request['code'];

            $image = Image::make($file)->resize(600, null, function ($constraint) {
                $constraint->aspectRatio();
            })->encode($file->getClientOriginalExtension(), $imageEncode);

            $thumb = Image::make($file)->resize(400, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode($file->getClientOriginalExtension(), $thumbEncode);

            $imagePath = "$directoryName/" . $filename . "." . $this->request->file('image')->getClientOriginalExtension();
            $thumbPath = "$directoryName/thumbnails/" . $filename . "." . $this->request->file('image')->getClientOriginalExtension();

            if ($type === 'update') {
                $this->delete($data, $directoryName);
            }

            Storage::disk('image')->put($imagePath, $image);
            Storage::disk('image')->put($thumbPath, $thumb);

            $response = [
                'status' => true,
                'data' => [
                    "filename" => $filename . '.' .  $this->request->file('image')->getClientOriginalExtension(),
                    "imageURL" => Storage::disk("image")->url($imagePath),
                    "thumbURL" => Storage::disk('image')->url($thumbPath)
                ]
            ];
        } else {
            $response = [
                'status' => false,
                'message' => __('File Tidak Ditemukan')
            ];
        }
        return $response;
    }

    public function delete($file, $directoryName = 'categories')
    {
        $filename = json_decode($file['image'], true);

        Storage::disk('image')->delete([ '/' . $directoryName . '/' . $filename['filename'], '/' . $directoryName . '/thumbnails/' . $filename['filename']]);
    }
}
