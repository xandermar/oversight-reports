<?php

namespace Drupal\oversight_reports\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class ReportController.
 */
class ReportController extends ControllerBase {

  public function getTitle($id){
    return $this->getReportItem($id,'title','FIELD');
  }

  /**
   * Report.
   *
   * @return string
   *   Return Hello string.
   */
  public function report($id) {
    
    $view = '
          <div class="text-end mb-1">
            <a href="/reports" class="btn btn-primary">Select another Report</a>
          </div>
          <div class="container">
              <div class="row">
                  [data]
              </div>
              <div class="row">
                  [recommendations]
              </div>              
          </div>   
    ';    

    $fields = array(
        'field_submitting_oig'=>'Submitting OIG:',
        'body' => 'Description:',
        'summary' => 'Short / Alternative Report Title:',
        'field_publication_date' => 'Date Issued:',
        'field_component_agency_' => 'Agency Reviewed / Investigated:',
        'field_submitt_oig_report_number' => 'Submitting OIG-Specific Report Number:',
        'field_component_if_applicable' => 'Component, if applicable:',
        'field_address_locality' => 'Location(s):',
        'field_type_of_product' => 'Type of Report:',
        'field_net_questioned_costs' => 'Questioned Costs:',
        'field_net_funds_for_better_use' => 'Funds for Better Use:',
        'field_number_of_recommendations' => 'Number of Recommendations:',
        'field_upload_document' => 'View Document:'
    );
    $data='';
    foreach($fields as $machine_name => $field){
        $data_result = $this->getReportItem($id,$machine_name,'FIELD');

        $machine_name == 'field_net_questioned_costs' || 
        $machine_name == 'field_net_funds_for_better_use' 
          ? $data_result = '$'.number_format($data_result) : null;
        $machine_name == 'field_publication_date' ? $data_result = date('F j, Y', strtotime($data_result)) : null;       

        $machine_name == 'field_address_locality' && ($this->getReportItem($id,'field_agency_wide','FIELD')!=='AA')
          ? $loc = array(
            $this->getReportItem($id,'field_address_locality','FIELD'),
            $this->getReportItem($id,'field_address_administrative_area','FIELD'),
            $this->getReportItem($id,'field_agency_wide','FIELD'),
          ) : ($machine_name == 'field_address_locality' && ($this->getReportItem($id,'field_agency_wide','FIELD')=='AA') 
            ? $data_result = 'Agency-Wide' : null );

        $machine_name == 'field_address_locality' && is_array($loc)
          ? $data_result = $this->compileLocation($loc) : null;   
          
        $machine_name == 'field_upload_document' 
        ? $data_result = $this->filterFilename($data_result) : null;             

        $data_result ?
        $data .= '
          <div class="col-sm-3 left-rail text-end">'.$field.'</div>
          <div class="col-sm-9 right-rail">'.$data_result.'</div>        
        ' : null;
    }  

    // data
    $view = str_replace('[data]',$data,$view);

    // report title
    $view = str_replace('[title]',$this->getReportItem($id,'title','FIELD'),$view);
    
    // report pdf
    $view = str_replace('[field_upload_document]','<a target="_blank" href="'.$this->getReportItem($id,'field_upload_document','FIELD').'">'.$this->getReportItem($id,'field_upload_document','FIELD').'</a>',$view);

    // recommendations
    $view = str_replace('[recommendations]',$this->recommendations($id,$this->getReportItem($id,'uuid','FIELD'),$this->getReportItem($id,'field_number_of_recommendations','FIELD')),$view);
    
    return [
      '#type' => 'markup',
      '#markup' => $this->t($view),
      '#allowed_tags' => ['div','table'],
      '#attached' => ['library' => ['oversight_reports/oversight_reports']],
    ];
  }

  public function recommendations($id,$uuid,$n){
    $config = $this->config('oversight_reports.default');

    $fields = array(
        'field_rec_number',
        'field_status',
        'field_significant_rec',
        'field_text_of_rec_trimmed',
    );    



    $rec_table = '
      <h2 id="recommendations-results-title">Related Open Recommendations</h2>
      <table id="recommendations-results-table" class="display" style="width:100%">
        <thead>
          <tr>
            <th scope="col">Recommendation Number</th>
            <th scope="col">Recommendation Status</th>
            <th scope="col">Significant Recommendation</th>
            <th scope="col">Additional Details</th>
          </tr>
        </thead>
        <tbody>'.$this->getRecommendationItem($id,$uuid).'</tbody>
      </table>    
    ';

    $return_no_results = '
    <div id="recommendations-no-results">
      <div>Related Open Recommendations</div>
      <div>No Recommendations found for this Report.</div>
    </div>
    ';

    $n > 0 ? $r = $rec_table : $r = $return_no_results;
    return $config->get('include_recommendations') ? $r : null;
  }

  public function filterFilename($f){
    $filename = explode('/',$f);
    $url = '<img src="https://www.oversight.gov/modules/file/icons/application-pdf.png"> <a href="'.$f.'">'.end($filename).'</a>';
    return $url;
  }

  public function compileLocation($loc){
    $clean = array();

    // clean up array
    foreach($loc as $l)
      $l ? array_push($clean,$l) : null; 
  
    $l = implode(', ',$clean);
    return $l;
  }

  public function report_template(){
    $ret = '
          <div><a href="/reports">Select another Report</a></div>
          <h2>[title]</h2>
          <div class="container">
              <div class="row">
                  [data]
              </div>
          </div>   
    ';

    return $ret;
  }

  // function getReportItem($id,$reportId,$field){
  function getReportItem($reportId,$machine_name,$field){ 

      $reports = json_decode(file_get_contents(drupal_get_path('module', 'oversight_reports').'/reports.json'));
      foreach($reports->nodes as $report){
          if($report->Nid == $reportId){
              return $report->$machine_name;
          }   
      }
  } 
  
  function getRecommendationItem($id,$reportUUID){ 

      $recommendations = json_decode(file_get_contents(drupal_get_path('module', 'oversight_reports').'/recommendations.json'));
      $ret = '';
      foreach($recommendations->nodes as $recommendation){
          if($recommendation->field_report_uuid == $reportUUID){
            $status = $recommendation->field_status ? 'OPEN' : 'CLOSED';
            $significant = $recommendation->field_significant_rec ? 'Yes' : 'No';
            $ret .= '
            <tr>
              <td>'.$recommendation->field_rec_number.'</td>
              <td>'. $status .'</td>
              <td>'. $significant .'</td>
              <td>
                <a href="/reports/'.$id.'/'.$recommendation->field_report_uuid.'/'.$recommendation->field_rec_number.'">'.$recommendation->field_text_of_rec_trimmed.'</a>
              </td>
            </tr>
            '; 
          }   
      }
      return $ret;
  }   

}