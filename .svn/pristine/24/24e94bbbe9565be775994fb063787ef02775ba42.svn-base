<?php

use Carbon\Carbon;

 use Illuminate\Support\Facades\DB;
 use App\Models\department_group_heirarchy;
 use App\Models\proll_reference_data;
 use App\Models\group;
    //for get job family and job group
    function org_structure_level($department_group_heirarchy_id,$department_group_id){

                //our old structure
                if(!$department_group_heirarchy_id && $department_group_id){
                    $job_parent_name = DB::table('department_group3')->where('department_group3_id','=',$department_group_id)->first();
                    $job_child_name =  DB::table('department_group2')->where('department_group2_id','=',$job_parent_name->department_group2_id)->first();
                }else{

                //our new strcture
                    $job_parent_name = department_group_heirarchy::where('id','=',$department_group_heirarchy_id)->first();

                    $job_child_name = department_group_heirarchy::where('id','=',$job_parent_name["parent_id"])->first();

                }
                return array("job_parent_name"=>$job_parent_name,"job_child_name"=>$job_child_name);
    }
    //get eet group
    function get_group(){
        return group::where('groupTitle','=','EET')->first();
    }
    function replace_carriage_return($replace, $string)
    {
        return str_replace(array("\n\r", "\n", "\r"), $replace, $string);
    }
    function get_reference($filter_name,$id)
    {

        $reference = proll_reference_data::where('id','=',$id)->wherehas('proll_reference_data_code',function($q) use($filter_name){

            $q->where('reference_code','=',$filter_name);
        })->select('ref_id','description','id','reference_key')->first();

        if(!$reference){
            return null;
        }
        else
        {
            return $reference;
        }
    }
    function get_approval_queue($approval_queue)
    {
        $approval_queue = explode(',', $approval_queue);


        $approval_queue_mapping = [];

        foreach($approval_queue as $queue){
            if($queue == "External LM Two"){
                $approval_queue_mapping[] = "second_reporting_to_id";

            }if($queue == "External LM One"){
                $approval_queue_mapping[] = "reporting_to_id";
            }if($queue == "Department Lms"){
                $approval_queue_mapping[] = "dept_id";
            }
        }
        $approval_queue_mapping = implode(',', array_filter($approval_queue_mapping));

        return $approval_queue_mapping;
    }
     function getCompanies(){
        return DB::table('proll_client')->select('id','companyname as name')->get();
    }
    function getCountries(){
            return DB::table('countries')->select('country_id as id','country as name')->where('display','=',1)->get();
    }
    function getStatesByCountry($id){
            return DB::table('states')->select('id','name')->whereIn('country_id',$id)->get();
    }
    function getCitiesByState($id){
            return DB::table('cities')->select('id','name')->whereIn('state_id',$id)->get();
    }
    function getBranchesByCity($id){
            return DB::table('proll_client_location')->select('loc_id as id','loc_desc as name')->whereIn('city_id',$id)->get();
    }
    function getGeoGroups(){
            $data= DB::table('geo_groups')->select('field_name','label')->orderBy('parent_id','ASC')->get();
            if($data) {
                $data[0]->options = getGeoZoneGroupValues();
                return $data;
            }else{
                return false;
            }
    }
    function getGeoZoneGroupValues($id=1){
            return DB::table('geo_company_groups')->select('id','label as name')->where('geo_group_id',$id)->get();
    }
    function getGeoGroupsByParentId($request){
        $id=$request->id;
        $field_name=$request->filter_name;
        if($field_name=='companies'){
            return DB::table('geo_company_groups as c')
                ->select('c.id', 'c.label as name')
                ->where('c.parent_id', 0)->get();
        }else{
            if(isLastGeoGroup($field_name)){
                return getCountriesByGeoGroup($id);
            }else{
                return DB::table('geo_company_groups as c')
                    ->select('c.id', 'c.label as name')
                    ->whereIn('c.parent_id', $id)->get();
            }
        }


    }
    function getGeoGroupsByCompany($id){
        return DB::table('geo_company_groups as c')
            ->select('c.id', 'c.label as name')
            ->whereIn('c.company_id', $id)
            ->where('c.parent_id', 0)->get();
    }
    function getCountriesByGeoGroup($id){
            return DB::table('geo_groups_countries as g')
                ->leftJoin('countries as c', 'c.country_id','=','g.country_id')
                ->select('c.country_id as id', 'c.country as name')
                ->whereIn('g.geo_company_group_id', $id)->distinct()->get();
    }
    function getCountriesByGeoGroups($geo_group,$id){
            return DB::table('geo_groups_countries as g')
                ->leftJoin('countries as c', 'c.country_id','=','g.country_id')
                ->leftJoin('geo_company_groups as cg', 'cg.id','=','g.geo_company_group_id')
                ->leftJoin('geo_groups', 'geo_groups.id','=','cg.geo_group_id')
                ->select('c.country_id as id', 'c.country as name')
                ->where('geo_groups.field_name', $geo_group)
                ->whereIn('g.geo_company_group_id', $id)->distinct()->get();
    }
    function isLastGeoGroup($field_name){
            return !DB::table('geo_groups as g')
                ->where('g.parent_id','=',DB::table('geo_groups as g')->where('g.field_name','=',$field_name)->pluck('id'))->exists();
    }
    function getCriteriaSetupFeilds($setup_type){
            $data= DB::table('hr_criteria_setup_fields as f')
                ->leftJoin('hr_setup_type_criteria_fields as sf','f.id','=','sf.criteria_setup_field_id')
                ->where('sf.hr_setup_type_id','=',$setup_type)->get();
            return getCriteriaSetupFeildsData($data);
    }
    function getCriteriaSetupFeildsData($data){
        $res=array();
        foreach ($data as $row){
            $res[]=array("criteria"=> $row->label,
                    "control"=>
                    array("field_type"=>$row->field_type,
                        "field_name"=>$row->field_name,
                        "multiple"=>$row->multiple),
                    "value"=>(empty($row->reference_data_code)?getCriteriaSetupFeildDataByField($row):getCriteriaSetupFeildsKeyDescriptionByReferenceCode($row))
                );

        }
        return $res;
    }
    function getCriteriaSetupFeildDataByField($field,$ids=array()){
        if($field->table_name){
            return DB::table($field->table_name)
            ->select("$field->table_pk_col as id","$field->table_desc_col as value")
            ->when($ids, function($query) use ($ids,$field) {
                return $query->whereIn($field->table_pk_col,$ids);
            })->get();
        }
        return [];
    }
    function getCriteriaSetupFeildsKeyDescriptionByReferenceCode($field,$ids=array()){
        return DB::table('proll_reference_data as r')
            ->leftJoin('proll_reference_data_code as d','r.ref_id','=','d.ref_id')
            ->where('d.reference_code','=',$field->reference_data_code)
            ->select("$field->table_pk_col as id","$field->table_desc_col as value")
            ->when($ids, function($query) use ($ids,$field) {
                return $query->whereIn($field->table_pk_col,$ids);
            })->get();
        }
    function getKeyDescriptionByReferenceCode($reference_code){
            return DB::table('proll_reference_data as r')
                ->leftJoin('proll_reference_data_code as d','r.ref_id','=','d.ref_id')
                ->where('d.reference_code','=',$reference_code)
                ->select("reference_key as id","description as name")->get();
            }

      function getIdDescriptionByReferenceCode($reference_code){
        return DB::table('proll_reference_data as r')
            ->leftJoin('proll_reference_data_code as d','r.ref_id','=','d.ref_id')
            ->where('d.reference_code','=',$reference_code)
            ->select("id","description as name")->get();
    }
    function getKeyValueByTable($table, $key, $val){
                return DB::table($table)
                    ->select("$key as id","$val as name")->get();
                }

      function getSubDepartmentsByDepartment($id){
        return DB::table('department_hierarchy as d')
            ->select('d.id','d.department_name as value')
            ->when($id, function($query) use ($id) {
                if(in_array(-1,$id)){
                    return $query->where('d.type','=','sub_department');
                }else{
                    return $query->whereIn('d.reporting_department_id',$id);
                }
            })->get();
        }

      function getSetupTypeId($setup_type)
    {
        return DB::table('hr_setup_types')->where('setup_type',$setup_type)->pluck('id')->first();

        }

      function getGeoGroupIdByName($group)
    {
        return DB::table('geo_groups')->where('field_name',$group)->pluck('id')->first();
    }

      function getCriteriaFieldByName($name)
    {
        return DB::table('hr_criteria_setup_fields')->where('field_name',$name)->first();
    }

      function getEnumValues($table, $column) {
        $type = DB::select(DB::raw("SHOW COLUMNS FROM $table WHERE Field = '{$column}'"))[0]->Type ;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $enum = array();
        foreach( explode(',', $matches[1]) as $value )
        {
            $v = trim( $value, "'" );
            $enum[]=array('id'=>$v,'name'=>$v);
        }
        return $enum;
    }

    function parseDate($date, $month = null, $year = null){
        $d_m_y = null;
        if($date && $month == null && $year == null){
            $d_m_y = Carbon::parse($date)->format("Y-m-d");
        }elseif($date && $month){
            $d_m_y = Carbon::parse($date)->format("m");
        }elseif($date && $year){
            $d_m_y = Carbon::parse($date)->format("Y");
        }
        return $d_m_y;

    }
