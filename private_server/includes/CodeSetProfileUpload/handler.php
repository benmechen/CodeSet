<?php
/**
 * @Author: Ben
 * @Date: 2017-04-12 23:55:59
 * @Project: codeset.co.uk
 * @File Name: handler.php
 * @Last Modified by:   Ben
 * @Last Modified time: 2017-08-29 22:52:04
**/
include '../../../config.php';

$uid = $auth->getUIDFromHash($auth->getSessionHash())['uid'];

$user = $auth->getUser($uid);

$target_dir = "../../../public_html/assets/images/profiles/";
$extension = end(explode(".", basename($_FILES["account-profile-file"]["name"])));
$target_file = $target_dir . "user-" . $uid . "." . strtolower($extension);
$uploadOk = 1;
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Check if image file is an image
if(isset($_POST["account-profile-change-submit"])) {
    $check = getimagesize($_FILES["account-profile-file"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        $uploadOk = 0;
    }
}

if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    header('Location: /public_html/pages/private/account.php');
// Try to upload file
} else {
    if ($user['profile'] != "user-default.png" && file_exists($target_dir . $user['profile'])) {
        unlink($target_dir . $user['profile']);    
    }

    if (move_uploaded_file($_FILES["account-profile-file"]["tmp_name"], $target_file)) {
        
        // Update path in database
        $auth->changeProfile($uid, "user-" . $uid . "." . strtolower($extension));

        // Rotate image if needed
        $image = imagecreatefromstring(file_get_contents($target_file));
        $exif = exif_read_data($target_file);
        
        if(isset($exif['Orientation'])) {
            switch($exif['Orientation']) {
                case 8:
                    $image = imagerotate($image,90,0);
                    break;
                case 3:
                    $image = imagerotate($image,180,0);
                    break;
                case 6:
                    $image = imagerotate($image,-90,0);
                    break;
                default:
                    break;
            }

            switch(strtolower($extension)) {
                    case 'jpg':
                        imagejpeg($image, $target_file, 90);
                        break;
                    case 'jpeg':
                        imagejpeg($image, $target_file, 90);
                        break;
                    case 'png':
                        imagepng($image, $target_file);
                        break;
                    case 'gif':
                        imagegif($image, $target_file);
                        break;
                    default:
                        // Unsupported format
                        break;
                }
        }
        imagedestroy($image);

        // Crop image to square
        function square_crop($src_image, $dest_image, $thumb_size = 200, $jpg_quality = 90) {

            // Get dimensions of existing image
            $image = getimagesize($src_image);

            // Check for valid dimensions
            if( $image[0] <= 0 || $image[1] <= 0 ) return false;

            // Determine format from MIME-Type
            $image['format'] = strtolower(preg_replace('/^.*?\//', '', $image['mime']));

            // Import image
            switch( $image['format'] ) {
                case 'jpg':
                case 'jpeg':
                    $image_data = imagecreatefromjpeg($src_image);
                    break;
                case 'png':
                    $image_data = imagecreatefrompng($src_image);
                    break;
                case 'gif':
                    $image_data = imagecreatefromgif($src_image);
                    break;
                default:
                    // Unsupported format
                    return false;
                    break;
            }

            // Verify import
            if( $image_data == false ) return false;

            // Calculate measurements
            if( $image[0] > $image[1] ) {
                // For landscape images
                $x_offset = ($image[0] - $image[1]) / 2;
                $y_offset = 0;
                $square_size = $image[0] - ($x_offset * 2);
            } else {
                // For portrait and square images
                $x_offset = 0;
                $y_offset = ($image[1] - $image[0]) / 2;
                $square_size = $image[1] - ($y_offset * 2);
            }

            // Resize and crop
            $canvas = imagecreatetruecolor($thumb_size, $thumb_size);
            if( imagecopyresampled( $canvas, $image_data, 0, 0, $x_offset, $y_offset, $thumb_size, $thumb_size, $square_size, $square_size)) {
                // Create thumbnail
                switch( strtolower(preg_replace('/^.*\./', '', $dest_image)) ) {
                    case 'jpg':
                    case 'jpeg':
                        return imagejpeg($canvas, $dest_image, $jpg_quality);
                        break;
                    case 'png':
                        return imagepng($canvas, $dest_image);
                        break;
                    case 'gif':
                        return imagegif($canvas, $dest_image);
                        break;
                    default:
                        // Unsupported format
                        return false;
                        break;
                }

            } else {
                imagedestroy($image);
                imagedestroy($canvas);
                return false;
            }
            imagedestroy($image);
            imagedestroy($canvas);
            return True;
        }

        square_crop($target_file, $target_file);
        
    }
}

header('Location: /public_html/pages/private/account?redirect=True.php');