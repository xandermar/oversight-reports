<?php

namespace Drupal\oversight_reports\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class RecommendationsController.
 */
class RecommendationsController extends ControllerBase {

  /**
   * Recommendation.
   *
   * @return string
   */
  public function recommendation($id, $rec_id, $rec_number) {
    $rec_table = '
      <table>
        <tbody>'.$this->recTableRow($rec_id,$rec_number).'</tbody>
      </table>    
      <h2>Associated Report Details</h2>
      <table>
        <tbody>
          <tr>
            <th>Linked Report</th>
            <td><a href="/reports/'.$id.'">'.$this->getLinkedReport($rec_id,$rec_number).'</a></td>
          </tr> 
          </tbody>
      </table>          
    ';    
    return [
      '#type' => 'markup',
      '#markup' => $this->t($rec_table),
    ];
  }

  public function getLinkedReport($rec_id,$rec_number){
    $recommendations = json_decode(file_get_contents(drupal_get_path('module', 'oversight_reports').'/recommendations.json'));
    foreach($recommendations->nodes as $recommendation){
      if(
        $recommendation->field_report_uuid == $rec_id &&
        $recommendation->field_rec_number == $rec_number
      ){ return $recommendation->field_report_title; }
    }
  }

  public function recTableRow($rec_id,$rec_number){
    $recommendations = json_decode(file_get_contents(drupal_get_path('module', 'oversight_reports').'/recommendations.json'));
    $ret = '';
    foreach($recommendations->nodes as $recommendation){
      if(
        $recommendation->field_report_uuid == $rec_id &&
        $recommendation->field_rec_number == $rec_number
      ){
        $status = $recommendation->field_status ? 'Open' : 'Closed';    
        $significant = $recommendation->field_significant_rec ? 'Yes' : 'No';
        $ret .= '
          <tr>
            <th>Text of Recommendation</th>
            <td>'.$recommendation->field_text_of_rec.'</td>
          </tr>     
          <tr>
            <th>Recommendation Number</th>
            <td>'.$recommendation->field_rec_number.'</td>
          </tr>             
          <tr>
            <th>Recommendation Status</th>
            <td>'.$status.'</td>
          </tr>     
          <tr>
            <th>Significant Recommendation</th>
            <td>'.$significant.'</td>
          </tr>      
          <tr>
            <th>Recommendation Questioned Costs</th>
            <td>$'.number_format($recommendation->field_rec_net_questioned_costs).'</td>
          </tr>                   
          <tr>
            <th>Recommendation Funds for Better Use</th>
            <td>$'.number_format($recommendation->field_rec_net_funds_better_use).'</td>
          </tr>          
        ';
      }
    }

    return $ret;
  }

}