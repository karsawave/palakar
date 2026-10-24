<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Resources\Validation;

final class ValidationTest extends TestCase
{
    private function form(array $rules): Validation
    {
        return new class($rules) extends Validation {
            public function __construct(private array $rules)
            {
                parent::__construct();
            }

            public function setRules(): array
            {
                return $this->rules;
            }

            public function noAdmin(string $field, mixed $value, string $label): bool
            {
                if ($value === 'admin') {
                    $this->setErrorMessage($field, $label.' is reserved');

                    return false;
                }

                return true;
            }
        };
    }

    public function testRequiredFieldMissingFromInputIsReported(): void
    {
        // Panada 1 skipped this: the loop ran over the input, not the rules.
        $form = $this->form(['email' => ['rules' => ['required'], 'label' => 'Email']]);

        $this->assertFalse($form->validate(['name' => 'someone']));
        $this->assertSame('Email can not be empty', $form->errorMessages('email'));
    }

    public function testZeroIsNotEmpty(): void
    {
        $this->assertTrue($this->form(['qty' => ['rules' => ['required']]])->validate(['qty' => '0']));
    }

    public function testAutoSanitizeTrimsAndRemovesControlCharacters(): void
    {
        $form = $this->form(['name' => ['rules' => ['required']]]);

        $this->assertTrue($form->validate(['name' => "  Bu\0di\x07  "]));
        $this->assertSame('Budi', $form->value('name'));
        $this->assertFalse($form->validate(['name' => "   \t  "]));
    }

    public function testNamedAndCallableFilters(): void
    {
        $form = $this->form([
            'email' => ['rules' => ['email'], 'filter' => ['lower']],
            'age' => ['rules' => ['integer'], 'filter' => ['int']],
            'phone' => ['filter' => ['digits']],
            'bio' => ['filter' => ['strip_tags', 'ucfirst']],
        ]);

        $this->assertTrue($form->validate(['email' => ' BUDI@Example.COM ', 'age' => '17', 'phone' => '+62 812-3456', 'bio' => '<b>halo</b>']));
        $this->assertSame(['email' => 'budi@example.com', 'age' => 17, 'phone' => '628123456', 'bio' => 'Halo'], $form->value());
    }

    public function testOnlyFieldsWithRulesAreReturned(): void
    {
        $form = $this->form(['name' => ['rules' => ['required']]]);
        $form->validate(['name' => 'Budi', 'is_admin' => '1']);

        $this->assertSame(['name' => 'Budi'], $form->value());
    }

    public function testOptionalEmptyFieldSkipsOtherRules(): void
    {
        $form = $this->form(['website' => ['rules' => ['url']]]);

        $this->assertTrue($form->validate(['website' => '']));
        $this->assertFalse($form->validate(['website' => 'javascript:alert(1)']));
        $this->assertTrue($form->validate(['website' => 'https://panada.dev']));
    }

    public function testRules(): void
    {
        $cases = [
            [['min' => 3], 'ab', false], [['min' => 3], 'abc', true],
            [['max' => 3], 'abcd', false], [['max' => 4], 'ééé', true],
            [['match' => 4], '1234', true], [['in' => ['a', 'b']], 'c', false],
            [['regex' => '/^\d{3}$/'], '123', true], [['alpha'], 'Budi', true],
            [['alpha'], 'Budi1', false], [['numeric'], '-1.5', true],
            [['integer'], '1.5', false], [['alphanumeric'], 'abc123', true],
            [['email'], 'not-an-email', false], [['email'], 'a@b.co', true],
        ];

        foreach ($cases as [$rules, $value, $expected]) {
            $this->assertSame($expected, $this->form(['f' => ['rules' => $rules]])->validate(['f' => $value]), json_encode([$rules, $value]));
        }
    }

    public function testCompareAndCallback(): void
    {
        $form = $this->form([
            'username' => ['rules' => ['required', 'callback' => 'noAdmin'], 'label' => 'Username'],
            'password' => ['rules' => ['required']],
            'password_confirm' => ['rules' => ['compare' => 'password'], 'label' => 'Confirmation'],
        ]);

        $this->assertFalse($form->validate(['username' => 'admin', 'password' => 'secret1', 'password_confirm' => 'secret2']));
        $this->assertSame('Username is reserved', $form->errorMessages('username'));
        $this->assertStringContainsString('did not match', $form->errorMessages('password_confirm'));
        $this->assertNull($form->value('password'));
    }

    public function testArrayFieldsAreValidatedPerItem(): void
    {
        $form = $this->form(['tags' => ['rules' => ['alpha'], 'filter' => ['lower']]]);

        $this->assertTrue($form->validate(['tags' => ['PHP', 'Panada']]));
        $this->assertSame(['php', 'panada'], $form->value('tags'));
        $this->assertFalse($form->validate(['tags' => ['ok', 'not ok']]));
    }

    public function testErrorMessagesWithTags(): void
    {
        $form = $this->form(['a' => ['rules' => ['required']], 'b' => ['rules' => ['required']]]);
        $form->validate(['x' => 1]);

        $this->assertSame('<li>a can not be empty</li><li>b can not be empty</li>', $form->errorMessages(false, '<li>', '</li>'));
    }
}
