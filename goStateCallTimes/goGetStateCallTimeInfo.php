<?php
 /**
 * @file 		getStateCallTimesInfo.php
 * @brief 		API for State Calltimes
 * @copyright 	Copyright (C) GOautodial Inc.
 * @author		Jeremiah Sebastian Samatra  <jeremiah@goautodial.com>
 * @author     	Chris Lomuntad  <chris@goautodial.com>
 *
 * @par <b>License</b>:
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

    $state_call_time_id = $astDB->escape($_REQUEST["state_call_time_id"]);

	if($state_call_time_id == null) {
		$apiresults = array("result" => "Error: Set a value for State Call Time ID.");
	} else {
		$groupId = go_get_groupid($goUser, $astDB);

		if (!checkIfTenant($groupId, $goDB)) {
			$ul = "";
		} else {
			//$ul = "AND user_group='$groupId'";
			//$addedSQL = "WHERE user_group='$groupId'";
			$astDB->where('user_group', $groupId);
		}

		//$query = "SELECT state_call_time_id, state_call_time_state, state_call_time_name, sct_default_start, sct_default_stop, user_group from vicidial_state_call_times WHERE state_call_time_id='".mysqli_real_escape_string($state_call_time_id)."' $ul $addedSQL ORDER BY state_call_time_id LIMIT 1;";
		$astDB->where('state_call_time_id', $state_call_time_id);
		$astDB->orderBy('state_call_time_id', 'desc');
		$rsltv = $astDB->getOne('vicidial_state_call_times', 'state_call_time_id, state_call_time_state, state_call_time_name, sct_default_start, sct_default_stop, user_group');
		$exist = $astDB->getRowCount();
		if($exist > 0){
			foreach ($rsltv as $fresults){
				$dataStateID[] = $fresults['state_call_time_id'];
				$dataStateState[] = $fresults['state_call_time_state'];
				$dataStateName[] = $fresults['state_call_time_name'];
				$dataDefStart[] = $fresults['sct_default_start'];
				$dataDefStop[] = $fresults['sct_default_stop'];
				$dataUserGroup[] = $fresults['user_group'];
				$apiresults = array("result" => "success", "state_call_time_id" => $dataStateID, "state_call_time_state" => $dataStateState, "state_call_time_name" => $dataStateName, "sct_default_start" => $dataDefStart, "sct_default_stop" => $dataDefStop, "user_group" => $dataUserGroup);
			}
		} else {
			$apiresults = array("result" => "Error: Lead Filter does not exist.");
		}
	}
?>