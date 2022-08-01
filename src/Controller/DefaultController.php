<?php

namespace Drupal\oversight_reports\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  public function getJson($id,$import){

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://www.oversight.gov/api/v4/'.$import.'/filtered/'.$id,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_HTTPHEADER => array(
        'Cookie: DRUPAL_UID=-1; cookiesession1=037F9184WSM4DXZBYSP22TSDJ7BU5763; php-console-server=5'
      ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    $file = drupal_get_path('module', 'oversight_reports').'/'.$import.'.json';
    file_put_contents($file, $response);    

  }

  /**
   * Reports.
   *
   * @return string
   *   Return Hello string.
   */
  public function reports() {

    $config = $this->config('oversight_reports.default');
    $refresh_interval = $config->get('refresh_interval') * 60;

    // reports
    $reports_age = time()-filemtime(drupal_get_path('module', 'oversight_reports').'/reports.json');
    $reports_age > $refresh_interval ? $this->getJson($config->get('agency_id'),'reports') : null;

    // recommendations
    $recommendations_age = time()-filemtime(drupal_get_path('module', 'oversight_reports').'/recommendations.json');
    $recommendations_age > $refresh_interval ? $this->getJson($config->get('agency_id'),'recommendations') : null;    
    
    $t .= '
    <table id="oversight-reports" class="display" style="width:100%">
      <thead>
          <tr>
            <th>Report Date (YYYY-MM-DD)</th>
            <th>Title</th>
            <th>Type</th>
            <th>Location</th>
          </tr>
      </thead>
      <tbody>'.$this->getReportList().'</tbody>    
        <tfoot>
          <tr>
            <th>Report Date (YYYY-MM-DD)</th>
            <th>Title</th>
            <th>Type</th>
            <th>Location</th>
          </tr>
        </tfoot>      
    </table>
    ';    

    return [
      '#type' => 'markup',
      '#markup' => $this->t($t),
      '#attached' => ['library' => ['oversight_reports/oversight_reports']],
    ];
  }

  function getReportList(){
      $reports = json_decode(file_get_contents(drupal_get_path('module', 'oversight_reports').'/reports.json'));
      $ret = '';
      foreach($reports->nodes as $report){
          $loc = array();
          $report->field_address_locality ? array_push($loc,$report->field_address_locality) : null;
          $report->field_address_administrative_area ? array_push($loc,$report->field_address_administrative_area) : null;
          $report->field_agency_wide ? array_push($loc,$report->field_agency_wide) : null;

          $loc = implode(', ',$loc);
          if($loc=='AA' || $loc == ''){
            $loc = 'Agency-Wide';
          }
          
          $ret .= '
          <tr>
              <td class="report-field_publication_date">'.str_replace(' 00:00:00','',$report->field_publication_date).'</td>
              <td class="report-title"><a href="/reports/'.$report->Nid.'">'.$report->title.'</a></td>
              <td class="report-field_type_of_product">'.$report->field_type_of_product.'</td>
              <td class="report-field_agency_wide">'.$loc.'</td>
          </tr>';
      }
      return $ret;
  }  

}