<div class="gbb-row-wrapper" id="home_news_div">
  <div class=" gbb-row bg-size-cover">
    <div class="bb-inner default">
      <div class="bb-container container">
        <div class="row">
          <div class="row-wrapper clearfix">
            <div class="news_home_title">
              <h2> Recent News   </h2>
              <p> See what's going on insider our company  </p>
            </div>
            <div class="news_home_more">
              <a href="#" class="btn-theme">Read More</a>
            </div>
          </div>
          <div class="row-wrapper clearfix">
            <?php
            global $base_url;
            global $base_site_url;
            use Drupal\node\Entity\Node;
            use Drupal\Core\Url;
            use Drupal\media\Entity\Media;
            use Drupal\file\Entity\File;
            $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
            $sql = "SELECT node.nid nid , node.title title , body.body_value body , news_date.field_news_date_value news_date , img.field_news_image_target_id news_image FROM node_field_data node inner join node__body body on (node.nid = body.entity_id) inner join node__field_news_date news_date on (node.nid = news_date.entity_id) inner join node__field_news_image img on (node.nid = img.entity_id) WHERE node.type ='news' and node.langcode= '$language'  and body.langcode='$language'  ORDER by nid DESC LIMIT 3";
            //     print $sql;exit();
            $database = \Drupal::database();
            $result = $database->query($sql);
            $i =1;
            while ($row_data = $result->fetchAssoc()) {
              $news_nid   = $row_data['nid'];
              $news_title = $row_data['title'];
              $news_desc  = $row_data['body'];
              $news_img   = $row_data['news_image'];
              $file = File::load($news_img   );
              $url = $file->url();
              $news_date  = $row_data['news_date'];
              $news_link   = $base_site_url."/node/$news_nid";
              print'
                <div class="gsc-column col-lg-4 col-md-4 col-sm-12 col-xs-12">
              <div class="column-inner bg-size-cover ">
                <div class="column-content-inner">
                  <div class="gsc-image-content skin-v1">
                    <div class="image">
                      <a href="'.$news_link.'">
                        <img src="'.$url.'" alt="'.$news_title.'">
                      </a>
                    </div>
                    <div class="content">
                      <h3 class="title">'. $news_title.'</h3>
                      <div class="desc">
                        <p>'. strip_tags($news_desc).'</p>
                      </div>
                      <div class="action">
                        <span ><i class="fa fa-clock-o" aria-hidden="true"></i>'.date('d-M-Y',$news_date).'</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
                ';
            }
            ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


