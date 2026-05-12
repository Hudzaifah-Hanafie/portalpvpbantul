<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

trait HasMathCaptcha
{
    protected function prepareCaptcha(string $sessionKey, bool $forceNew = false): array
    {
        if ($forceNew || !session()->has($sessionKey)) {
            session([$sessionKey => $this->buildCaptchaPayload()]);
        }

        return session($sessionKey);
    }

    protected function buildCaptchaPayload(): array
    {
        $first = random_int(3, 9);
        $second = random_int(1, 9);
        $operator = random_int(0, 1) === 1 ? '+' : '-';

        if ($operator === '-' && $second > $first) {
            [$first, $second] = [$second, $first];
        }

        $answer = $operator === '+' ? $first + $second : $first - $second;

        return [
            'question' => sprintf('%d %s %d = ?', $first, $operator, $second),
            'answer' => $answer,
        ];
    }

    protected function validateCaptcha(Request $request, string $sessionKey, string $fieldName = 'captcha_answer'): void
    {
        $captcha = $this->prepareCaptcha($sessionKey);
        $answer = trim((string) $request->input($fieldName));

        if ($answer === '' || (int) $answer !== (int) $captcha['answer']) {
            $this->prepareCaptcha($sessionKey, true);

            throw ValidationException::withMessages([
                $fieldName => 'Jawaban keamanan tidak sesuai. Silakan coba lagi.',
            ]);
        }

        session()->forget($sessionKey);
    }
}
