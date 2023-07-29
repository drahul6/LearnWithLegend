

              <script>
                      
                  function Testimonial(data) {
                      this.data = data;

                      this.showVideoInput = function(){
                          return this.data.type == 'video';
                      }
                  }

                app.controller('TestimonialController', function($scope, $http) {

                    $testimonial_id = <?=$testimony->id;?>;
                    $http.get($base_url+"/user/fetch_testimonial/"+$testimonial_id)
                    .then(function(response) {

                      $scope.$testimonial = new Testimonial(response.data);

                    });


                });



              </script>

                  <?=$testimony->DisplayStatus;?>
                  <?=$testimony->DisplayPublishedStatus;?>

                  <form  ng-controller="TestimonialController" ng-cloak id="testimony_edit" 
                  class=" ajax_form" action="<?= domain; ?>/<?=$accessor ?? 'user';?>/update_testimonial" method="post">
                    <input type="hidden" name="testimony_id" value="<?= $testimony->id; ?>">


                    <?php if ($accessor == 'admin') :?>
                    <div class="form-group">
                      <label>Attester</label>
                      <input required="" ng-model="$testimonial.data.attester" class="form-control" name="attester" placeholder="John Doe">
                    </div>
                    <?php endif  ;?>




                    <div class="form-group">
                      <label>Type</label> 
                      <select ng-model="$testimonial.data.type" class="form-control" name="type">
                          <option value="">Select Type</option>
                          <option ng-selected="$testimonial.data.type==value" ng-repeat="(key, value) in ['written', 'video' ]" >{{value}}</option>
                      </select>

                    </div>


                    <div class="form-group">
                      <label>Intro</label>
                      <input  ng-model="$testimonial.data.intro" class="form-control" name="intro" placeholder="Eg CEO lodash" value="<?= $testimony->intro; ?>">
                    </div>


                    <div class="form-group" ng-if="$testimonial.showVideoInput()">
                      <label>Link to Video</label>
                      <input required="" type="url" ng-model="$testimonial.data.video_link" class="form-control" name="video_link" placeholder="Enter video Link e.g https://www.youtube.com/watch?v=xxxx" value="<?= $testimony->video_link; ?>">
                    </div>

                    <div class="form-group" ng-if="!$testimonial.showVideoInput()">
                      <label>Write your testimonial</label>
                      <div class="">
                        <textarea ng-model="$testimonial.data.content" placeholder="Write your testimonial" class="form-control textarea" name="testimony" placeholder="" style="height: 150px"></textarea>
                      </div>
                    </div>


                    <div class="">
                      <button type="submit" class="btn btn-white">Submit</button>
                    </div>
                  </form>
