<?php

namespace Tatter\Firebase\Firestore;

use CodeIgniter\Validation\Validation;
use CodeIgniter\Validation\ValidationInterface;
use DomainException;

/**
 * Validation Trait
 *
 * Adds validation methods to Collection.
 * Adapted from the framework's BaseModel.
 */
trait ValidationTrait
{
    /**
     * Whether we should limit fields in inserts
     * and updates to those available in $allowedFields or not.
     *
     * @var bool
     */
    protected $protectFields = true;

    /**
     * Our validator instance.
     *
     * @var ValidationInterface
     */
    protected $validation;

    /**
     * Rules used to validate data in add and update methods.
     * The array must match the format of data passed to the Validation library.
     *
     * @var array<string,string>
     */
    protected $validationRules = [];

    /**
     * Contains any custom error messages to be
     * used during data validation.
     *
     * @var array<string,string>
     */
    protected $validationMessages = [];

    /**
     * Skip the model's validation. Used in conjunction with skipValidation()
     * to skip data validation for any future calls.
     *
     * @var bool
     */
    protected $skipValidation = false;

    /**
     * @return $this
     */
    public function setValidation(ValidationInterface $validation): self
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Set the value of the skipValidation flag.
     *
     * @param bool $skip Value
     *
     * @return $this
     */
    public function skipValidation(bool $skip = true): self
    {
        $this->skipValidation = $skip;

        return $this;
    }

    /**
     * Returns the model's defined validation rules so that they
     * can be used elsewhere, if needed.
     *
     * @param array $options Options
     */
    public function getValidationRules(array $options = []): array
    {
        $rules = $this->validationRules;

        if (isset($options['except'])) {
            $rules = array_diff_key($rules, array_flip($options['except']));
        } elseif (isset($options['only'])) {
            $rules = array_intersect_key($rules, array_flip($options['only']));
        }

        return $rules;
    }

    /**
     * Validates the data against the validation rules/group.
     *
     * @param array<string, mixed> $data
     */
    public function validate(array $data, bool $cleanValidationRules = true): bool
    {
        $rules = $this->getValidationRules();

        if ($this->skipValidation || $rules === [] || $data === []) {
            return true;
        }

        $rules = $cleanValidationRules ? $this->cleanValidationRules($rules, $data) : $rules;

        // If no data existed that needs validation our job is done here.
        if ($rules === []) {
            return true;
        }

        return $this->validation->setRules($rules, $this->validationMessages)->run($data, null);
    }

    /**
     * Returns any validation errors that occurred.
     *
     * @return array<string,string> [field => message]
     */
    public function getErrors(): array
    {
        if ($this->skipValidation) {
            return [];
        }

        return $this->validation->getErrors();
    }

    /**
     * Ensures that only the fields that are allowed to be updated
     * are in the data array.
     * Used by add() and change() to protect against mass assignment
     * vulnerabilities.
     *
     * @throws DomainException
     */
    protected function protectFields(array $data): array
    {
        if (! $this->protectFields) {
            return $data;
        }

        if ($this->allowedFields === []) {
            throw new DomainException('You must define the "allowedFields" to use field protection.');
        }

        $allowedFields = array_merge($this->allowedFields, [$this->primaryKey]);

        foreach (array_keys($data) as $key) {
            if (! in_array($key, $allowedFields, true)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Removes any rules that apply to fields that have not been set
     * currently so that rules don't block updating when only updating
     * a partial row.
     *
     * @param array      $rules Array containing field name and rule
     * @param array|null $data  Data
     */
    protected function cleanValidationRules(array $rules, ?array $data = null): array
    {
        if (empty($data)) {
            return [];
        }

        foreach (array_keys($rules) as $field) {
            if (! array_key_exists($field, $data)) {
                unset($rules[$field]);
            }
        }

        return $rules;
    }
}
