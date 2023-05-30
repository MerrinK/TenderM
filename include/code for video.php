
<!-- Code for displaying video -->
<?php if( $sdata['0']->video_upload && file_exists( './uploads/assets/video/'.$sdata['0']->video_upload )) { 
   $video = base_url('uploads/assets/video/'.$sdata['0']->video_upload); 
   echo '<video width="300" controls>
             <source src="'.$video.'" type="video/mp4">
             <source src="'.$video.'" type="video/ogg">
             <source src="'.$video.'" type="video/avi">
             <source src="'.$video.'" type="video/wmv">
             <source src="'.$video.'" type="video/mov">
        </video>';
   }else{
        echo '<img src="'.repo_assets().'plugins/dropify/src/images/icon-upload.png">';
        // $img = repo_assets().'plugins/dropify/src/images/icon-upload.png'; 
   }
?>