<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Courses / ') }} {{ $course->code }}
        </h2>
    </x-slot>
    <div class="max-w-2xl mx-auto p-4 sm:p-6 lg:p-8">
        @if(is_null($course))
            <div class="bg-red-200 text-red-700 p-2.5 m-2">
                <strong>Unable to find course!</strong>
            </div>
        @else
        <div class="divide-y">
            <div class="p-6 flex space-x-2">
                <div class="flex-1">
                    <div class="flex justify-between items-center mt-1">
                        <div>
                            <span class="font-bold text-lg">{{ $course->code }}: {{$course->name}}</span>
                            <span class="ml-1.5 text-gray-700">{{ $course->duration }}</span>
                        </div>
                    </div>
                    <x-show-ratings :course="$course" ml="1"></x-show-ratings>
                    <div>
                        <small class="ml-2 text-sm text-gray-600">{{ $course->prereqCreditCount }} credits required</small>
                        <small class="ml-2 text-sm text-gray-600">{{ $course->prereqCreditCountMajor }} major credits required</small>
                    </div>
                    <div>
                        @if($course->minimumGrade)
                            <small class="ml-2 mt-2 text-sm text-red-600">{{ __('A minimum grade of ')}}{{ $course->minimumGrade }}{{ __('% is required to continue the program')}}</small>
                        @endif
                    </div>
                    @if(is_array($course->concentration))
                        @if($course->concentration[0] != "")
                            <div>
                                <small class="ml-2 text-sm text-gray-600">
                                    <b>{{ __('Concentrations: ') }}</b>
                                    @foreach($course->concentration as $conc)
                                        {{ $conc }}
                                        @if($loop->remaining > 0)
                                            {{ __(', ')}}
                                        @endif
                                    @endforeach
                                </small>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        <div class = "mt-2">
            <p class="font-bold">Prerequisite Courses</p>
            <div class="p-2 bg-white border border-gray-300 block w-full focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
            >@php
                $out = "";
                if(!is_null($course->prereqs)) {
                    foreach($course->prereqs as $cc=>$cn) {
                        $out.= $cn.", ";
                    }
                    $out = substr($out, 0, -2);
                    echo $out;
                }
                if($out == "")
                    echo "None";
            @endphp
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
