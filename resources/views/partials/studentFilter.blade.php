

<script type="text/javascript">


    function openAjax() {

        var ajax;
        try{
            ajax = new XMLHttpRequest();
        }catch(ee){
            try{
                ajax = new ActiveXObject("Msxml2.XMLHTTP");
            }catch(e){
                try{
                    ajax = new ActiveXObject("Microsoft.XMLHTTP");
                }catch(E){
                    ajax = false;
                }
            }
        }
        return ajax;
    };

    function CpForm(FormName){
        comp = "document." + FormName;
        var frm = eval(comp);
        Cps = "";
        for (i=0; i<frm.length; i++){
            Cps = Cps + frm.elements[i].name + "=" + frm.elements[i].value + "&";
        }
        Cps = Cps.substring(0,Cps.length -1);
        return Cps;
    }

    function OpenAjaxPostCmd(pagina,camada,values,msg,divcarga,metodo,tpmsg) {
        if(document.getElementById) {
            var ajax = openAjax();
            if(tpmsg=='1'){
                loading.style.visibility = 'visible';
            }
            var exibeResultado = document.getElementById(camada);
            if(metodo=='1'){
                ajax.open("POST", pagina, true);
                ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                ajax.setRequestHeader("Cache-Control", "no-store, no-cache, must-revalidate");
                ajax.setRequestHeader("Cache-Control", "post-check=0, pre-check=0");
                ajax.setRequestHeader("Pragma", "no-cache");
                valor = CpForm(values)
            }else{
                valor = null;
                ajax.open("GET", pagina + values, true);
            }
            ajax.onreadystatechange = function() {
                if(ajax.readyState == 1) {
                    if(tpmsg=='1'){
                        exibeLoading.style.visibility = 'visible';


                    }
                }
                if(ajax.readyState == 4) {
                    if(tpmsg=='1'){
                        exibeLoading.innerHTML = "";
                        exibeLoading.style.visibility = 'hidden';
                    }
                    if(ajax.status == 200) {
                        var resultado = null;
                        resultado = ajax.responseText;
                        resultado = resultado.replace(/\+/g," ");
                        resultado = unescape(resultado);
                        exibeResultado.innerHTML = resultado;

                    } else {
                        exibeResultado.innerHTML = "<br / ><br / ><center>An error occurred:</center><br / ><br / > <center>" + resultado + "</center>";
                    }
                }
            }
            ajax.send(valor);
        }
    }

</script>


<?php

$ajax='ajax'

?>



                            <form action="{{ url()->current(). '/' .'generalExport' }}" method="post"  name="appfilter" id="appfilter">
                                <div class="row">
                                    <div class="form-group col-md-3">
                                            <input name="fname_x" type="text" class="form-control" id="fname_x" placeholder="{{trans('student.first_name')}}" onkeyup="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                                    </div>

                                    <div class="form-group col-md-3">

                                            <input name="name_x" type="text" class="form-control" id="name_x" placeholder="{{trans('student.last_name')}}" onkeyup="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">

                                    </div>

                                    <div class="form-group col-md-3">
                                            <input placeholder="{{trans('student.student_id')}}" name="id" type="text" class="form-control" id="id" onkeyup="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">

                                    </div>

                                    <div class="form-group col-md-3">
                                            <select name="country_id" class="form-control select2" id="country_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                                                <option value="">{{trans('student.select_country')}}</option>
                                        @foreach($countries as $country)
                                            <option value="{{@$country->id}}">{{@$country->name}}</option>
                                            @endforeach
                                            </select>


                                    </div>

                                </div>




    <div class="row">
           <div class="form-group col-md-3">

                    <select name="section_id" class="form-control select2" id="section_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                        <option value="">{{trans('student.select_section')}}</option>
                        @foreach($sections as $school)
                            <option value="{{@$school->id}}">{{@$school->title}}</option>
                        @endforeach
                    </select>
           </div>

        <div class="form-group col-md-3">


                    <select name="direction_id" class="form-control select2" id="direction_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                        <option value="">{{trans('student.select_program')}}</option>
                        @foreach($directions as $programme)
                            <option value="{{$programme->id}}">{{$programme->title}}</option>
                        @endforeach
                    </select>

        </div>

        <div class="form-group col-md-3">


                     <select name="company_year_id" class="form-control select2" id="company_year_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                        <option value="">{{trans('student.select_academic_year')}}</option>
                        @foreach($schoolyears as $academicyear)
                            <option value="{{$academicyear->id}}">{{$academicyear->title}}</option>
                        @endforeach
                    </select>
        </div>

        <div class="form-group col-md-3">

                     <select name="semester_id" class="form-control select2" id="semester_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                            <option value="">{{trans('student.select_semester')}}</option>
                            @foreach($semesters as $semester)
                                <option value="{{$semester->id}}">{{$semester->title}}({{$semester->school_year->title}})</option>
                            @endforeach
                        </select>
        </div>

    </div>


     <div class="row">
            <div class="form-group col-md-3">

                <select name="entry_mode_id" class="form-control select2" id="entry_mode_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                        <option value="">{{trans('student.select_entry_mode')}}</option>
                        @foreach($entrymodes as $entrymode)
                            <option value="{{$entrymode->id}}">{{$entrymode->name}}</option>
                        @endforeach
                    </select>
            </div>

         <div class="form-group col-md-3">

          <select name="gender" class="form-control select2" id="gender" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                <option value="*" selected="SELECTED">{{trans('student.select_gender')}}</option>

                <option value="1">Male</option>
                <option value="0">Female</option>

            </select>

         </div>

         <div class="form-group col-md-3">

             <select name="marital_status_id" class="form-control select2" id="marital_status_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                <option value="" selected="SELECTED">{{trans('student.select_marital_status')}}</option>
                @foreach($maritalStatus as $marital)
                    <option value="{{$marital->id}}">{{$marital->name}}</option>
                @endforeach
            </select>
         </div>

         <div class="form-group col-md-3">
            <select name="intake_period_id" class="form-control select2" id="intake_period_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                <option value="">{{trans('student.select_intake_period')}}</option>
                @foreach($intakeperiods as $intakeperiod)
                    <option value="{{$intakeperiod->id}}">{{$intakeperiod->name}}</option>
                @endforeach
            </select>

         </div>

     </div>

     <div class="row">
            <div class="form-group col-md-3">

            <select name="session_id" class="form-control select2" id="session_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                <option value="">Select Session</option>
                @foreach($sessions as $session)
                    <option value="{{$session->id}}">{{$session->name}}</option>
                @endforeach
            </select>
            </div>

         <div class="form-group col-md-3">

             <select name="level_id" class="form-control select2" id="level_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                <option value="">{{trans('student.select_level')}}</option>
                @foreach($levels as $level)
                    <option value="{{$level->id}}">{{$level->name}}</option>
                @endforeach
            </select>
         </div>

         <div class="form-group col-md-3">
             <select name="religion_id" class="form-control select2" id="religion_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                <option value="">{{trans('student.select_religion')}}</option>
                @foreach($religions as $religion)
                    <option value="{{$religion->id}}">{{$religion->name}}</option>
                @endforeach
            </select>
         </div>


         <div class="form-group col-md-3">
             <select name="graduation_year_id" class="form-control select2" id="graduation_year_id" onChange="OpenAjaxPostCmd('{{ url('/ajax/'.$type) }}','result_container','appfilter','Please wait','result_container','1','2')">
                 <option value="">{{trans('student.select_graduation_year')}}</option>
                 @foreach($graduationyears as $graduationyear)
                     <option value="{{$graduationyear->id}}">{{$graduationyear->title}}</option>
                 @endforeach
             </select>
         </div>

     </div>



    <input name="_token" type="hidden" id="_token" value="{{ csrf_token() }}">
  <div class="row">
    <button type="submit" class="btn btn-sm btn-warning pull-right">{{trans('dashboard.export')}}</button>
  </div>
    </form>



