<?php

namespace App\Model;

use Symfony\Component\Console\Style\SymfonyStyle as BaseSymfonyStyle;

class SymfonyStyle extends BaseSymfonyStyle {

    /**
     * Display task starting information with provided message.
     *
     * @param string $message
     */
    public function taskStart(string $message) {
        $this->writeln(
            sprintf(" ➤ %s", $message)
        );
    }

    /**
     * Display information about the task that will be processed.
     *
     * @param string $message
     */
    public function taskProcess(string $message) {
        $this->writeln(
            sprintf(" .. %s", $message)
        );
    }

    /**
     * Successfully finish previous task.
     * This function replace arrow icon from previous task with check icon.
     */
    public function taskEnd() {
        $this->writeln("\r\033[K\033[1A\r <info>✔</info>");
    }

    /**
     * Finish previous task with error.
     * This function replace arrow icon from previous task with cross icon.
     */
    public function taskError() {
        $this->writeln("\r\033[K\033[1A\r <fg=yellow>✘</fg=yellow>");
    }

    /**
     * Successfully finish previous task.
     * This function don't integrate into previous task message
     * Just add new line with provided message and check icon.
     *
     * @param string $message
     */
    public function taskSuccess(string $message = "Done") {
        $this->writeln(
            sprintf(" <info>✔ %s</info>", $message)
        );
    }

    /**
     * Display success information with provided message.
     *
     * @param string $message
     */
    public function writeInfo(string $message) {
        $this->writeln(
            sprintf(" <info>%s</info>", $message)
        );
    }

    /**
     * Display error information with provided message.
     *
     * @param string $message
     */
    public function writeError(string $message) {
        $this->writeln(
            sprintf(" <error>%s</error>", $message)
        );
    }

    /**
     * Display warning information with provided message.
     *
     * @param string $message
     */
    public function writeWarning(string $message) {
        $this->writeln(
            sprintf(" <comment>%s</comment>", $message)
        );
    }
}
