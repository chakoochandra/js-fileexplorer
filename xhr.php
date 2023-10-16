<?php

require_once "server-side-helpers/file_explorer_fs_helper.php";

$token = FileExplorerFSHelper::GetRequestVar('xsrftoken');

FileExplorerFSHelper::HandleActions("action", "file_explorer_", 'F:\\www\\hello-library\\js-fileexplorer\\files', [
    "base_url" => "http://localhost/hello-library/js-fileexplorer/files/",
    "protect_depth" => -1,  // Protects base_dir + additional directory depth.
    "recycle_to" => "Recycle Bin",
    "temp_dir" => "F:\www\hello-library\js-fileexplorer\tmp",
    "dot_folders" => false,  // Set to true to allow things like:  .git, .svn, .DS_Store
    "allowed_exts" => ".jpg, .jpeg, .png, .gif, .svg, .txt",
    "allow_empty_ext" => true,
    "thumbs_dir" => "F:\www\hello-library\js-fileexplorer\thumbs",
    "thumbs_url" => "http://localhost/hello-library/js-fileexplorer/thumbs/",
    "thumb_create_url" => "http://localhost/hello-library/js-fileexplorer/?action=file_explorer_thumbnail&xsrftoken={$token}",
    "refresh" => true,
    "rename" => true,
    "file_info" => false,
    "load_file" => false,
    "save_file" => false,
    "new_folder" => true,
    "new_file" => ".txt",
    "upload" => true,
    "upload_limit" => 20000000,  // -1 for unlimited or an integer
    "download" => true,
    "copy" => true,
    "move" => true,
    "delete" => true
]);
