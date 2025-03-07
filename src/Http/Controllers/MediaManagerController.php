<?php

namespace Jatdung\MediaManager\Http\Controllers;

use Dcat\Admin\Layout\Content;
use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Jatdung\MediaManager\MediaManager;
use Jatdung\MediaManager\MediaManagerServiceProvider;

class MediaManagerController extends Controller
{
    public function index(Content $content, Request $request)
    {
        Admin::requireAssets('@jatdung.media-manager');

        $path = $request->get('path', '/');
        $view = $request->get('view', 'table');

        $manager = new MediaManager($path);

        return $content
            ->title(MediaManagerServiceProvider::trans('media.title'))
            ->description(MediaManagerServiceProvider::trans('media.description'))
            ->body(Admin::view("jatdung.media-manager::index", [
                'list' => $manager->ls(),
                'nav'  => $manager->navigation($view),
                'view' => $view,
                'path' => $path
            ]));
    }

    public function download(Request $request)
    {
        $file = $request->get('file');

        $manager = new MediaManager($file);

        try {
            return $manager->download();
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function upload(Request $request)
    {
        $files = $request->file('files');
        $dir = $request->get('dir', '/');

        $manager = new MediaManager($dir);

        try {
            $manager->upload($files);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => trans('admin.uploader.upload_success_message', ['success' => count($files)])
        ]);
    }

    public function delete(Request $request)
    {
        $files = $request->get('files');

        $manager = new MediaManager();

        try {
            $manager->delete($files);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
        return response()->json([
            'status'  => true,
            'message' => trans('admin.delete_succeeded'),
        ]);
    }

    public function move(Request $request)
    {
        $path = $request->get('path');
        $new = $request->get('new');

        $manager = new MediaManager($path);

        try {
            $manager->move($new);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status'  => true,
            'message' => MediaManagerServiceProvider::trans('media.move_succeeded'),
        ]);
    }

    public function newFolder(Request $request)
    {
        $dir = $request->get('dir');
        $name = $request->get('name');

        $manager = new MediaManager($dir);

        try {
            if (!$manager->newFolder($name)) {
                throw new \Exception(MediaManagerServiceProvider::trans('media.new_folder_failed'));
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
        return response()->json([
            'status'  => true,
            'message' => MediaManagerServiceProvider::trans('media.new_folder_succeeded'),
        ]);
    }
}
