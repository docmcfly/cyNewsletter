<?php
declare(strict_types = 1);
namespace Cylancer\CyNewsletter\Domain\Model;


/**
 *
 * This file is part of the "Newsletter" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) 2024 Clemens Gogolin <service@cylancer.net>
 *
 * @package Cylancer\CyNewsletter\Domain\Model
 */
class ValidationResults
{

    /** @var array */
    protected $infos = array();

    /** @var array  */
    protected $errors = array();

    /**
     *
     * @return array of srings
     */
    public function getInfos(): array
    {
        return $this->infos;
    }

    /**
     *
     * @return array of srings
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     *
     * @return bool
     */
    public function getHasErrors(): bool
    {
        return $this->hasErrors();
    }

    /**
     *
     * @return bool
     */
    public function hasInfos(): bool
    {
        return ! empty($this->infos);
    }

    /**
     *
     * @param string $errorKey
     * @param array $arguments
     */
    public function addInfo(String $infoKey, array $arguments = []): void
    {
        $keySplit = explode('.', $infoKey, 2);
        $this->infos['info.' . $infoKey]['arguments'] = $arguments;
        $this->infos['info.' . $infoKey]['id'] = count($keySplit) == 2 ? $keySplit[0] : $infoKey;
    }

    /**
     *
     * @param string $errorKey
     * @param array $arguments
     */
    public function addError(String $errorKey, array $arguments = []): void
    {
        $keySplit = explode('.', $errorKey, 2);
        $this->errors['error.' . $errorKey]['arguments'] = $arguments;
        $this->errors['error.' . $errorKey]['id'] = count($keySplit) == 2 ? $keySplit[0] : $errorKey;
    }

    /**
     *
     * @param array $infos
     */
    public function addInfos(array $infos): void
    {
        foreach ($infos as $info) {
            $this->addInfo($info);
        }
    }

    /**
     *
     * @param array $errors
     */
    public function addErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }
    }
}
