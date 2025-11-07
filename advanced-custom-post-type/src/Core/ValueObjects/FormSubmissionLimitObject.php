<?php

namespace ACPT\Core\ValueObjects;

class FormSubmissionLimitObject implements \JsonSerializable
{
    const ANONYMOUS_USER = 'anonymous_user';
    const LOGGED_USER = 'logged_user';
    const SPECIFIC_USER = 'specific_user';
    const USER_ROLE = 'user_role';

    /**
     * @var string
     */
    private $rule;

    /**
     * @var array
     */
    private $uid = [];

    /**
     * @var array
     */
    private $roles = [];

    /**
     * @var string
     */
    private $value;

    /**
     * @param $array
     * @return FormSubmissionLimitObject
     * @throws \Exception
     */
    public static function fromArray(array $array)
    {
        if(!isset($array['rule'])){
            throw new \Exception("Rule is mandatory");
        }

        return new FormSubmissionLimitObject($array['rule'], $array['uid'] ?? null, $array['roles'] ?? null, $array['value'] ?? null);
    }

    /**
     * FormSubmissionLimitObject constructor.
     * @param $rule
     * @param array $uid
     * @param array $roles
     * @param null $value
     */
    public function __construct(
        $rule,
        $uid = [],
        $roles = [],
        $value = null
    )
    {
        $this->setRule($rule);
        $this->uid = $uid ? $uid : [];
        $this->roles = $roles ? $roles : [];
        $this->value = $value;
    }

    /**
     * @param string $rule
     */
    public function setRule(string $rule): void
    {
        $allowedRules = [
            self::ANONYMOUS_USER,
            self::LOGGED_USER,
            self::SPECIFIC_USER,
            self::USER_ROLE,
        ];

        if(!in_array($rule, $allowedRules)){
            throw new \DomainException($rule . " is not allowed rule.");
        }

        $this->rule = $rule;
    }

    /**
     * @return string
     */
    public function getRule(): string
    {
        return $this->rule;
    }

    /**
     * @return int|null
     */
    public function getValue(): ?int
    {
        if($this->value !== null){
            return (int)$this->value;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getUid(): array
    {
        return $this->uid;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return bool
     */
    public function hasRuleMatched()
    {
        switch ($this->rule){
            case self::ANONYMOUS_USER:
                return !is_user_logged_in();

            case self::LOGGED_USER:
                return is_user_logged_in();

            case self::SPECIFIC_USER:

                if(is_user_logged_in() === false){
                    return false;
                }

                $user = wp_get_current_user();

                if(empty($user->ID)){
                    return false;
                }

                return in_array($user->ID, $this->getUid());

            case self::USER_ROLE:

                if(is_user_logged_in() === false){
                    return false;
                }

                $user = wp_get_current_user();

                if(empty($user->roles)){
                    return false;
                }

                return !empty(array_intersect($user->roles, $this->getRoles()));
        }

        return false;
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'rule' => $this->getRule(),
            'uid' => $this->getUid(),
            'roles' => $this->getRoles(),
            'value' => $this->getValue(),
        ];
    }
}
