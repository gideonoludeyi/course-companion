<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Students / ') }} {{ $student->name }}
        </h2>
    </x-slot>
    <div class="max-w-3xl mx-auto p-4 sm:p-6 lg:p-8">
        @if(is_null($student))
            <div class="bg-red-200 text-red-700 p-2.5 m-2">
                <strong>Unable to find student!</strong>
            </div>
        @else
        <div class="divide-y">
                <div class="p-6 flex space-x-2">
                    <div class="flex-1">
                        <div class="flex justify-between items-center">
                            <div>
                                <span class="font-bold text-lg">{{ $student->name }}</a></span>
                                <span class="ml-1.5 text-gray-700 italic">{{ $student->number }}</span>
                                <span class="ml-3.5 text-gray-700">{{ $student->major }} {{ __('Major') }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            @if(!is_null($student->concentration))
                                <span class="ml-5 text-gray-700">{{ __('Concentration in ') }}{{ $student->concentration }}</span>
                            @else
                                <span class="ml-5 text-gray-700">{{ __('No concentration') }}</span>
                            @endif
                        </div>

                    </div>
                </div>
        </div>
        <div class="mt-2 flex justify-between w-full">
            <div class="w-full">
                <p class="font-bold">Credits Completed</p>
                <input readonly
                   type="text"
                   name="creditsCompleted"
                   value="{{$student->creditsCompleted}}"
                   class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                >
            </div>
            <div class="pl-1 w-full">
                <p class="font-bold">Major Credits Completed</p>
                <input readonly
                   type="text"
                   name="creditsCompletedMajor"
                   value="{{$student->creditsCompletedMajor}}"
                   class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                >
            </div>
        </div>
        <div class="mt-2 flex justify-between w-full">
            <div class="w-full">
                <p class="font-bold">First Year Credit <i>Maximum</i></p>
                <input readonly
                       type="text"
                       name="creditsCompleted"
                       value="{{$student->creditsCompletedFirstYear}} / 10.0"
                       class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                >
            </div>
            <div class="pl-1 w-full">
                <p class="font-bold">Second+ Year Elective <i>Minimum</i></p>
                <input readonly
                       type="text"
                       name="creditsCompletedMajor"
                       value="{{$student->electivesCompletedSecondYear}} / 3.0"
                       class="block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                >
            </div>
        </div>
        <div class = "mt-4">
            <p class="font-bold">Completed Courses</p>
            <div class="p-2 h-40 overflow-y-auto bg-white border border-gray-300 block w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                @foreach($student->completedCoursesV2 as $courseCompleted)
                    <div class="tooltip-container">
                        {{ $courseCompleted->code }}: {{$courseCompleted->name}}
                        <div class = "tooltip w-auto bg-gray-200 p-1 rounded-md ml-2">
                            <x-course-highlight :course="$courseCompleted" :p="1"></x-course-highlight>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class = "mt-4">
            <p class="font-bold">Eligible Courses Required by Major</p>
            <div class="p-2 h-40 overflow-y-auto bg-white border border-gray-300 block w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                @foreach($student->eligibleMajorCourses as $courseCompleted)
                    <div class="tooltip-container">
                        {{ $courseCompleted->code }}: {{$courseCompleted->name}}
                        @if($courseCompleted->minimumGrade)
                            <span class = "text-red-500">*{{ $courseCompleted->minimumGrade }}%</span>
                        @endif
                        <div class = "tooltip w-auto bg-gray-200 p-1 rounded-md ml-2">
                            <x-course-highlight :course="$courseCompleted" :p="1"></x-course-highlight>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <p class = "mt-1 text-red-500">{{ __('* Minimum grade required to continue major')}}</p>
        @if(isset($student->concentration))
        <div class = "mt-4">
            <p class="font-bold">Eligible Courses in Concentration</p>
            <div class="p-2 h-40 overflow-y-auto bg-white border border-gray-300 block w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                @foreach($student->eligibleConcentrationCourses as $courseCompleted)
                    <div class="tooltip-container">
                        {{ $courseCompleted->code }}: {{$courseCompleted->name}}
                        @if($courseCompleted->minimumGrade)
                            <span class = "text-red-500">*{{ $courseCompleted->minimumGrade }}%</span>
                        @endif
                        <div class = "tooltip w-auto bg-gray-200 p-1 rounded-md ml-2">
                            <x-course-highlight :course="$courseCompleted" :p="1"></x-course-highlight>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <p class = "mt-1 text-red-500">{{ __('* Minimum grade required to continue major')}}</p>
        @endif
        <div class = "mt-4">
            <p class="font-bold">Eligible Major Elective Courses</p>
            <div class="p-2 h-40 overflow-y-auto bg-white border border-gray-300 block w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                @foreach($student->eligibleElectiveMajorCourses as $courseCompleted)
                    <div class="tooltip-container">
                        {{ $courseCompleted->code }}: {{$courseCompleted->name}}
                        @if($courseCompleted->minimumGrade)
                            <span class = "text-red-500">*{{ $courseCompleted->minimumGrade }}%</span>
                        @endif
                        <div class = "tooltip w-auto bg-gray-200 p-1 rounded-md ml-2">
                            <x-course-highlight :course="$courseCompleted" :p="1"></x-course-highlight>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class = "mt-4">
            <p class="font-bold">Eligible Non-Major Elective Courses</p>
        <div class="p-2 h-40 overflow-y-auto bg-white border border-gray-300 block w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
            @foreach($student->eligibleElectiveCourses as $courseCompleted)
                <div class="tooltip-container">
                    {{ $courseCompleted->code }}: {{$courseCompleted->name}}
                    @if($courseCompleted->minimumGrade)
                        <span class = "text-red-500">*{{ $courseCompleted->minimumGrade }}%</span>
                    @endif
                    <div class = "tooltip w-auto bg-gray-200 p-1 rounded-md ml-2">
                        <x-course-highlight :course="$courseCompleted" :p="1"></x-course-highlight>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>
    </div>
</x-app-layout>
