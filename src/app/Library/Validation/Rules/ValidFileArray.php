<?php

namespace Backpack\CRUD\app\Library\Validation\Rules;

use Backpack\CRUD\app\Library\Validation\Rules\Support\HasFiles;
use Closure;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

abstract class ValidFileArray extends BackpackCustomRule
{
    use HasFiles;

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value = self::ensureValidValue($value)) {
            $fail('Unable to determine the value type.');

            return;
        }

        $this->validateArrayData($attribute, $fail, $value);
        $this->validateItems($attribute, $value, $fail);
    }

    public static function field(string|array|ValidationRule|Rule $rules = []): self
    {
        $instance = new static();
        $instance->fieldRules = self::getRulesAsArray($rules);

        if (! in_array('array', $instance->getFieldRules())) {
            $instance->fieldRules[] = 'array';
        }

        return $instance;
    }

    protected function validateItems(string $attribute, array $items, Closure $fail): void
    {
        if (is_multidimensional_array($items)) {
            $this->validateNestedItems($attribute, $items, $fail);

            return;
        }
        foreach ($items as $file) {
            $validator = Validator::make([$attribute => $file], [
                $attribute => $this->getFileRules(),
            ], $this->validator->customMessages, $this->validator->customAttributes);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() ?? [] as $attr => $message) {
                    foreach ($message as $messageText) {
                        $fail($messageText)->translate();
                    }
                }
            }
        }
    }

    private function validateNestedItems(string $attribute, array $items, Closure $fail)
    {
        $items = array_values(Arr::dot($items));
        $parent = Str::before($attribute, '.');
        $field = Str::afterLast($attribute, '.');

        foreach ($items as $key => $item) {
            if (! is_file($item)) {
                continue;
            }
            $validate = [$parent => [
                [
                    $field => $item,
                ],
            ]];
            $validator = Validator::make($validate, [$parent.'.*.'.$field => $this->getFileRules()], $this->validator->customMessages, $this->validator->customAttributes);

            if ($validator->fails()) {
                foreach ($validator->errors()->messages() ?? [] as $attr => $message) {
                    foreach ($message as $messageText) {
                        $fail($messageText)->translate();
                    }
                }
            }
        }
    }

    protected function validateArrayData(string $attribute, Closure $fail, null|array $data = null, null|array $rules = null): void
    {
        $data = $data ?? $this->data;
        $rules = $rules ?? $this->getFieldRules();

        $validator = Validator::make($data, [
            $attribute => $rules,
        ], $this->validator->customMessages, $this->validator->customAttributes);

        if ($validator->fails()) {
            foreach ($validator->errors()->messages()[$attribute] as $message) {
                $fail($message)->translate();
            }
        }
    }

    protected static function ensureValidValue($value)
    {
        if (! is_array($value)) {
            try {
                $value = json_decode($value, true);
            } catch (\Exception $e) {
                return false;
            }
        }

        return $value;
    }
}
