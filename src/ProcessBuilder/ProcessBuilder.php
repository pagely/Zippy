<?php
/*
 * This file was taken from the "symfony/process" for backward compatibility.
 * https://github.com/symfony/process/tree/3.4
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Alchemy\Zippy\ProcessBuilder;

use \InvalidArgumentException;
use \LogicException;
use Symfony\Component\Process\Process;

class ProcessBuilder
{
    private $arguments;
    private $cwd;
    private $env = array();
    private $input;
    private $timeout = 60;
    private $options = array();
    private $inheritEnv = true;
    private $prefix = array();
    private $outputDisabled = false;
    /**
     * Constructor.
     *
     * @param string[] $arguments An array of arguments
     */
    public function __construct(array $arguments = array())
    {
        $this->arguments = $arguments;
    }
    /**
     * Creates a process builder instance.
     *
     * @param string[] $arguments An array of arguments
     *
     * @return static
     */
    public static function create(array $arguments = array())
    {
        return new static($arguments);
    }
    /**
     * Adds an unescaped argument to the command string.
     *
     * @param string $argument A command argument
     *
     * @return $this
     */
    public function add($argument)
    {
        $this->arguments[] = $argument;
        return $this;
    }
    /**
     * Adds a prefix to the command string.
     *
     * The prefix is preserved when resetting arguments.
     *
     * @param string|array $prefix A command prefix or an array of command prefixes
     *
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = is_array($prefix) ? $prefix : array($prefix);
        return $this;
    }
    /**
     * Sets the arguments of the process.
     *
     * Arguments must not be escaped.
     * Previous arguments are removed.
     *
     * @param string[] $arguments
     *
     * @return $this
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
        return $this;
    }
    /**
     * Sets the working directory.
     *
     * @param null|string $cwd The working directory
     *
     * @return $this
     */
    public function setWorkingDirectory($cwd)
    {
        $this->cwd = $cwd;
        return $this;
    }
    /**
     * Sets whether environment variables will be inherited or not.
     *
     * @param bool $inheritEnv
     *
     * @return $this
     */
    public function inheritEnvironmentVariables($inheritEnv = true)
    {
        $this->inheritEnv = $inheritEnv;
        return $this;
    }
    /**
     * Sets an environment variable.
     *
     * Setting a variable overrides its previous value. Use `null` to unset a
     * defined environment variable.
     *
     * @param string      $name  The variable name
     * @param null|string $value The variable value
     *
     * @return $this
     */
    public function setEnv($name, $value)
    {
        $this->env[$name] = $value;
        return $this;
    }
    /**
     * Adds a set of environment variables.
     *
     * Already existing environment variables with the same name will be
     * overridden by the new values passed to this method. Pass `null` to unset
     * a variable.
     *
     * @param array $variables The variables
     *
     * @return $this
     */
    public function addEnvironmentVariables(array $variables)
    {
        $this->env = array_replace($this->env, $variables);
        return $this;
    }
    /**
     * Sets the input of the process.
     *
     * @param resource|scalar|\Traversable|null $input The input content
     *
     * @return $this
     *
     * @throws InvalidArgumentException In case the argument is invalid
     */
    public function setInput($input)
    {
        $this->input = ProcessUtils::validateInput(__METHOD__, $input);
        return $this;
    }
    /**
     * Sets the process timeout.
     *
     * To disable the timeout, set this value to null.
     *
     * @param float|null $timeout
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setTimeout($timeout)
    {
        if (null === $timeout) {
            $this->timeout = null;
            return $this;
        }
        $timeout = (float) $timeout;
        if ($timeout < 0) {
            throw new InvalidArgumentException('The timeout value must be a valid positive integer or float number.');
        }
        $this->timeout = $timeout;
        return $this;
    }
    /**
     * Adds a proc_open option.
     *
     * @param string $name  The option name
     * @param string $value The option value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;
        return $this;
    }
    /**
     * Disables fetching output and error output from the underlying process.
     *
     * @return $this
     */
    public function disableOutput()
    {
        $this->outputDisabled = true;
        return $this;
    }
    /**
     * Enables fetching output and error output from the underlying process.
     *
     * @return $this
     */
    public function enableOutput()
    {
        $this->outputDisabled = false;
        return $this;
    }
    /**
     * Creates a Process instance and returns it.
     *
     * @return Process
     *
     * @throws LogicException In case no arguments have been provided
     */
    public function getProcess()
    {
        if (0 === count($this->prefix) && 0 === count($this->arguments)) {
            throw new LogicException('You must add() command arguments before calling getProcess().');
        }
        $options = $this->options;
        $arguments = array_merge($this->prefix, $this->arguments);
        $script = implode(' ', array_map(array(__NAMESPACE__.'\\ProcessUtils', 'escapeArgument'), $arguments));
        $process = new Process($script, $this->cwd, $this->env, $this->input, $this->timeout, $options);
        if ($this->inheritEnv) {
            if (method_exists($process, 'inheritEnvironmentVariables')) {
                $process->inheritEnvironmentVariables();
            }
        }
        if ($this->outputDisabled) {
            if (method_exists($process, 'disableOutput')) {
                $process->disableOutput();
            }
        }
        return $process;
    }
}
