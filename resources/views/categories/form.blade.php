<div class="col-xs-12" xmlns:v-on="http://www.w3.org/1999/xhtml"
     xmlns:v-bind="http://www.w3.org/1999/xhtml">


    <div class="row">
        <div class="col-xs-12">
            <div class=" form-group border-grey-700">
                <p class="full-width border-lg p-20 text-bold text-size-large text-center text-uppercase">@{{ categoryFullName | html }} </p>
                <hr/>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            {!!  Form::label('gender', trans('core.gender'),['class' => 'text-bold' ]) !!}

            <select v-model="genderSelect" class="form-control">
                <option v-for="gender in genders" v-bind:value="gender.value">
                    @{{ gender.text }}
                </option>
            </select>

        </div>
        <div class="col-md-4 col-md-offset-2">

            <div class=" form-group">

                {!!     Form::label('isTeam', trans('categories.isTeam'),['class' => 'text-bold' ])  !!}
                <br/>

                <div>
                    <input type="radio" name="isTeam" id='yes' value="1" v-model="isTeam"/>
                    <label for="yes"> {{ trans('core.yes')  }}</label>

                    &nbsp;&nbsp;&nbsp;
                    <input type="radio" name="isTeam" id='no' value="0" v-model="isTeam"/>
                    <label for="no">{{ trans('core.no')  }}</label>
                </div>
            </div>

        </div>

    </div>
    <div class="row">

        <div class="col-md-4">
            <div class=" form-group">
                {!!  Form::label('ageCategory', trans('categories.ageCategory'),['class' => 'text-bold' ]) !!}
                <select v-model="ageCategorySelect" class="form-control">
                    <option v-for="ageCategory in ageCategories" v-bind:value="ageCategory.value">
                        @{{ decodeHtml(ageCategory.text) }}
                    </option>
                </select>
            </div>
        </div>
        <div class="col-md-4" v-if='ageCategorySelect==5'>
            <div class=" form-group">
                {!!  Form::label('ageMin', trans('categories.min_age'),['class' => 'text-bold' ]) !!}
                <select v-model="ageMin" class="form-control">
                    <option value="0">{{trans('categories.no_age_restriction')}}</option>
                    <option :value="n+6" v-for="n in 85">@{{n+6}}</option>

                </select>


            </div>
        </div>
        <div class="col-md-4" v-if='ageCategorySelect==5'>
            <div class=" form-group">
                {!!  Form::label('ageMax', trans('categories.max_age'),['class' => 'text-bold' ]) !!}
                <select v-model="ageMax" class="form-control">
                    <option value="0">{{trans('categories.no_age_restriction')}}</option>
                    <option :value="n+6" v-for="n in 85">@{{n+6}}</option>

                </select>
            </div>
        </div>


    </div>


    <div class="row">

        <div class="col-md-4">
            <div class=" form-group">
                {!!  Form::label('gradesSelect', trans('core.grade'),['class' => 'text-bold' ]) !!}

                <select v-model="gradeSelect" class="form-control">
                    <option value="0">{{trans('categories.no_grade_restriction')}}</option>
                    <option value="3">{{trans('categories.custom')}}</option>

                </select>
            </div>
        </div>
        <div class="col-md-4" v-if='gradeSelect==3'>
            <div class=" form-group">
                {!!  Form::label('gradeMin', trans('categories.min_grade'),['class' => 'text-bold' ]) !!}
                <select v-model="gradeMin" class="form-control" v-show="gradesSelect!=0">
                    <option v-for="(grade, val) in grades" :value="val.value">@{{ val.text | html }}</option>

                </select>


            </div>
        </div>
        <div class="col-md-4" v-if='gradeSelect==3'>
            <div class=" form-group">
                {!!  Form::label('gradeMax', trans('categories.max_grade'),['class' => 'text-bold' ]) !!}
                <select v-model="gradeMax" class="form-control" v-show="gradesSelect!=0">
                    <option v-for="(grade, val) in grades" v-bind:value="val.value">@{{ val.text | html }}</option>
                </select>
            </div>
        </div>

    </div>

</div>

{{--Grades : @{{ grades }}<br/>--}}
{{--isTeam @{{ isTeam }}<br/> - @{{ isTeam  }} <br/>--}}
{{--Gender @{{ genderSelect }} - @{{ genderSelect | selectText genders}} <br/>--}}
{{--ageCategorySelect @{{ ageCategorySelect }}<br/>--}}

{{--GradeSelect : @{{ gradeSelect }}<br/>--}}
{{--GradeMin : @{{ gradeMin}} GradeMax : @{{ gradeMax }}<br/>--}}
{{--AgeMin : @{{ ageMin }} AgeMax : @{{ ageMax }}<br/>--}}

{{--FullName : @{{ categoryFullName }}<br/>--}}

<script>

    var team = "{{trans('categories.isTeam')}}";
    var single = "{{trans('categories.single')}}";

    var no_age = "{{trans('categories.no_age_restriction')}}";
    var no_grade = "{{trans('categories.no_grade_restriction')}}";
    var childs = "{{trans('categories.children')}}";
    var students = "{{trans('categories.students')}}";
    var adults = "{{trans('categories.adults')}}";
    var masters = "{{trans('categories.masters')}}";
    var custom = "{{trans('categories.custom')}}";

    var male = "{{trans('categories.male')}}";
    var female = "{{trans('categories.female')}}";
    var mixt = "{{trans('categories.mixt')}}";

    var grade = "{{trans('categories.grade')}}";
    var age = "{{trans('categories.age')}}";
    var years = "{{trans('categories.years')}}";

</script>