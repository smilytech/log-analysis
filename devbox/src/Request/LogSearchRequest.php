<?php

namespace App\Request;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LogSearchRequest
{
    protected $validator;


    /**
     * @Assert\NotBlank(allowNull="true", message="Service Names cannot be blank")
     * @Assert\Type(
     *     type="array",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     */
    protected $services;

    /**
     * @Assert\NotBlank(allowNull="true", message="Status Code cannot be blank")
     * @Assert\Type(
     *     type="int",
     *     message="The provided statusCode value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Range(
     *      min = 200,
     *      max = 503,
     *      notInRangeMessage = "Statu Code must be between {{ min }} and {{ max }}",
     * )
     */
    protected $statusCode;

    /**
     * @Assert\NotBlank(allowNull="true", message="Start Date cannot be blank")
     * @Assert\DateTime(
     *     message = "Start Date is not valid, expected format is: 'Y-m-d H:i:s'"
     * )
     */
    protected $startDate;

    /**
     * @Assert\NotBlank(allowNull="true", message="End Date cannot be blank")
     * @Assert\DateTime(
     *     message = "End Date is not valid, expected format is: 'Y-m-d H:i:s'"
     * )
     */
    protected $endDate;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validate():ConstraintViolationListInterface
    {
        return $this->validator->validate($this);
    }

    public function getServices() {
        return $this->services;
    }

    public function setServices($services) {
        $this->services = $services;
    }

    public function getStatusCode() {
        return $this->statusCode;
    }

    public function setStatusCode($statusCode) {
        $this->statusCode = $statusCode;
    }

    public function getStartDate() {
        return $this->startDate;
    }

    public function setStartDate($startDate) {
        $this->startDate = $startDate;
    }

    public function getEndDate() {
        return $this->endDate;
    }

    public function setEndDate($endDate) {
        $this->endDate = $endDate;
    }

}
