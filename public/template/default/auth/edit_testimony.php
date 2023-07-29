<?php
$page_title = "Edit Testimony";
include 'includes/header.php';
?>
     
        <div class="page-wrapper">

            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Edit Testimony</h3>
<!-- 
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="">Blank</a>
                                    </li>
                                </ol>
                            </nav>
                        </div> -->
                    </div>
                    <!-- <div class="col-5 align-self-center">
                        <div class="customize-input float-right">
                            <select class="custom-select custom-select-set form-control bg-white border-0 custom-shadow custom-radius">
                                <option selected>Aug 19</option>
                                <option value="1">July 19</option>
                                <option value="2">Jun 19</option>
                            </select>
                        </div>
                    </div> -->
                </div>
            </div>
            
         
            <div class="container-fluid">
              
                    <section id="video-gallery" class="card">
                    
                      <div class="card-content">
                        <div class="card-body">
                          <form
                          id="testimony_edit"
                          class="ajax_form"
                          action="<?=domain;?>/user/update_testimonial" method="post" >
                          <input type="hidden" name="testimony_id" value="<?=$testimony->id;?>">


              <!-- 
                          <div class="form-group">

                            <select name="type" class="form-control">
                              <option value="">Select Type</option>
                              <?php foreach (['written','video'] as $key => $value):?>
                                  <option <?=(($testimony['type']==$value)) ?'selected':'';?> value="<?=$value;?>"> 
                                      <?=$value;?>
                                  </option>
                              <?php endforeach ;?>

                            </select>

                          </div>
               -->        

                          <?php if ($testimony->type == 'video') :?>

                          <div class="form-group">
                            <input required="" type="url" class="form-control" name="video_link" placeholder="Enter video Link e.g https://www.youtube.com/watch?v=xxxx" value="<?=$testimony->video_link;?>">
                          </div>

                          <?php endif  ;?>

                          <?php if ($testimony->type == 'written') :?>
                          <div class="form-group">
                            <div class="">
                              <textarea placeholder="Write your tesimonial" class="form-control textarea" name="testimony" placeholder="" style="height: 150px"><?=$testimony->content;?></textarea>
                            </div>
                          </div>
                          <?php endif ;?>

                          <div class="">
                            <button type="submit" class="btn btn-success">Save</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </section>


              



            </div>

<?php include 'includes/footer.php';?>
