<?php

namespace App\Http\Requests\Student;

use App\Models\Quiz;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

class FinishQuizRequest extends FormRequest
{
    public function authorize(): bool
    {
        $quiz = $this->route('quiz');

        return $quiz instanceof Quiz
            && $this->user()->can('finish', $quiz);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $quiz = $this->route('quiz');

        return [
            'answers' => ['required', 'array', 'min:1'],
            'answers.*' => ['required', 'array'],
            'answers.*.question_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('questions', 'id')->where('quiz_id', $quiz->id),
            ],
            'answers.*.answer_index' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(ValidationValidator $validator): void
    {
        $validator->after(function (ValidationValidator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $quiz = $this->route('quiz');
            $questions = $quiz->questions()->get(['id', 'options']);
            $answers = $this->input('answers', []);

            if (count($answers) !== $questions->count()) {
                $validator->errors()->add('answers', 'Pilih jawaban untuk setiap soal.');

                return;
            }

            $questionOptions = $questions->keyBy('id');

            foreach ($answers as $index => $answer) {
                $question = $questionOptions->get((int) $answer['question_id']);

                if ($question && (int) $answer['answer_index'] >= count($question->options)) {
                    $validator->errors()->add(
                        "answers.{$index}.answer_index",
                        'Pilihan jawaban tidak tersedia untuk soal ini.',
                    );
                }
            }
        });
    }
}
