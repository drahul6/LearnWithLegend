<?php
$page_title = "Testimonial";
include 'includes/header.php';
?>
     
        <div class="page-wrapper">

            <div class="page-breadcrumb">
                <div class="row">
                    <div class="col-7 align-self-center">
                        <h3 class="page-title text-truncate text-dark font-weight-medium mb-1">Testimonial</h3>
<!-- 
                        <div class="d-flex align-items-center">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0 p-0">
                                    <li class="breadcrumb-item"><a href="">Testimonial</a>
                                    </li>
                                </ol>
                            </nav>
                        </div> -->
                    </div>
                    <div class="col-5 align-self-center">
                        <div class="customize-input float-right">
                            <a class="btn btn-outline-dark" href="<?=domain;?>/user/create_testimonial"><i class="ft-plus"></i> Add</a>
                        </div>
                    </div>
                </div>
            </div>
            
         
            <div class="container-fluid">
              
              

                      <section id="video-gallery" class="card">
                        <div class="card-header">
                <!--           <h4 class="card-title">Testimonials</h4>
                 -->
                              <?php //include_once 'template/default/composed/filters/testimonials.php';?>
                          <div class="heading-elements">
                           <?=$note;?>
                          </div>
                        </div>
                        <div class="card-content">
                          <div class="card-body">
                            <div class="table-responsive">
                              
                            <table id="myTabe" class="table table-hover">
                              <thead>
                                <th>Sn</th>
                                <!-- <th style="width: 60%;">Letter</th> -->
                                <th>Video </th>
                                <th>Status <br> Date</th>
                                <th></th>
                              </thead>
                              <tbody>
                                <?php $i=1; foreach ($testimonials as $testimony) :?>
                                <tr>
                                  <td><?=$i;?></td>
                                  <!-- <td><?=$testimony->content;?></td> -->
                                  <td>
                                    <iframe width="560" height="315" src="<?=$testimony->video_link;?>" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe><br>
                                    <?=$testimony->video_link;?>
                                  </td>
                                  <td><?=$testimony->type;?><br><?=$testimony->DisplayStatus;?><br><?=$testimony->DisplayPublishedStatus;?>
                                  <span class="badge badge-primary"><?=$testimony->created_at;?></span>
                                      <div class="dropdown">
                                        <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">Action
                                        </button>
                                        <div class="dropdown-menu">

                                          <a  class="dropdown-item" href="<?=domain;?>/user/edit-testimony/<?=$testimony->id;?>" >Edit
                                          </a>
                                    

                                      </div>
                                    </div>


                                  </td>
                                </tr>
                                <?php $i++; endforeach ;?>
                              </tbody>
                            </table>

                          </div>
                          </div>
                        </div>
                      </section>


                      <ul class="pagination">
                          <?= $this->pagination_links($data, $per_page);?>
                      </ul>



            </div>

<?php include 'includes/footer.php';?>
