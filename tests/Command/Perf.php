<?php

declare(strict_types=1);

namespace CupcakeLabs\RedVelvet\Tests\Command;

use CupcakeLabs\RedVelvet\Converter\Blocks2Npf;
use CupcakeLabs\RedVelvet\Converter\Npf2Blocks;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Command
 *
 * Runs performance tests for Blocks to NPF and NPF to Blocks conversions.
 *
 *  Runs conversion performance tests for Blocks to NPF and NPF to Blocks.
 *
 *  ## OPTIONS
 *
 *  <file>
 *  : The file path containing the test data to be used.
 *
 *  [--csv=<csv>]
 *  : The file path to save performance metrics as CSV. Default is './performance_metrics.csv'.
 *
 *  ## EXAMPLES
 *
 *      php application.php redvelvet:perf /tmp/massive_test_data.json --csv=/tmp/performance_metrics.csv
 */
#[AsCommand(
    name: 'redvelvet:perf',
    description: 'Runs performance tests for Blocks to NPF and NPF to Blocks conversions.',
    aliases: ['perf'],
)]
class Perf extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::REQUIRED, 'The file path containing the test data to be used.')
            ->addArgument('csv', InputArgument::OPTIONAL, 'The file path to save performance metrics as CSV. Default is ./performance_metrics.csv.')
        ;
    }

    /**
     * @param InputInterface $input The input interface.
     * @param OutputInterface $output The output interface.
     * @return int
     *
     * @throws \JsonException If the JSON data is invalid.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file_path = $input->getArgument('file');
        $csv_path = $input->getArgument('csv');

        $csv_path_npf2gb = $csv_path ?? './npf2gb_metrics.csv';
        $csv_path_gb2npf = $csv_path ?? './gb2npf_metrics.csv';

        // Load test data from file
        if (!file_exists($file_path)) {
            $output->writeln("The file {$file_path} does not exist.");
            return Command::FAILURE;
        }

        $data = json_decode(file_get_contents($file_path), true, 1024, JSON_THROW_ON_ERROR);
        if (!isset($data['npf_posts'], $data['html_blocks']) || !$data) {
            $output->writeln("Invalid data format in {$file_path}.");
            return Command::FAILURE;
        }

        $blocks2npf = new Blocks2Npf();
        $npf2blocks = new Npf2Blocks();

        $csv_headers = array(
            'Iteration',
            'Conversion Type',
            'Conversion Time (ms)',
            'Memory Used (bytes)',
        );

        $csv_exists_npf2gb = file_exists($csv_path_npf2gb);
        $csv_exists_gb2npf = file_exists($csv_path_gb2npf);
        $handle_npf2gb = fopen($csv_path_npf2gb, 'ab');
        $handle_gb2npf = fopen($csv_path_gb2npf, 'ab');
        if (!$csv_exists_npf2gb) {
            fputcsv($handle_npf2gb, $csv_headers);
        }
        if (!$csv_exists_gb2npf) {
            fputcsv($handle_gb2npf, $csv_headers);
        }

        // Measure performance for Blocks to NPF conversion
        $output->writeln('Starting Blocks to NPF conversion...');
        $total_amount_blocks = count($data['html_blocks']);
        $total_blocks_to_npf_time = 0;
        $iteration = 1;
        foreach ($data['html_blocks'] as $block) {
            $execution_time = -hrtime(true);
            $blocks2npf->convert($block);
            $execution_time += hrtime(true);
            $final_memory = memory_get_usage();

            $execution_time_in_milliseconds = $execution_time / 1e+6;
            $total_blocks_to_npf_time += $execution_time_in_milliseconds;

            $csv_data = array(
                $iteration,
                'Blocks to NPF',
                $execution_time_in_milliseconds,
                $final_memory,
            );
            fputcsv($handle_gb2npf, $csv_data);
            ++$iteration;
        }
        fclose($handle_gb2npf);
        $output->writeln('Blocks to NPF conversion completed.');

        // Measure performance for NPF to Blocks conversion
        $output->writeln('Starting NPF to Blocks conversion...');
        $total_amount_npf_posts = count($data['npf_posts']);
        $total_npf_to_blocks_time = 0;
        $iteration = 1;
        foreach ($data['npf_posts'] as $npf) {
            $npf_text = json_encode($npf, JSON_THROW_ON_ERROR);
            $execution_time = -hrtime(true);
            $npf2blocks->convert($npf_text);
            $execution_time += hrtime(true);
            $final_memory = memory_get_usage();

            $execution_time_in_milliseconds = $execution_time / 1e+6;
            $total_npf_to_blocks_time += $execution_time_in_milliseconds;

            $csv_data = array(
                $iteration,
                'NPF to Blocks',
                $execution_time_in_milliseconds,
                $final_memory,
            );
            fputcsv($handle_npf2gb, $csv_data);
            ++$iteration;
        }
        fclose($handle_npf2gb);
        $output->writeln('NPF to Blocks conversion completed.');

        // Memory usage in bytes
        $peak_memory = memory_get_peak_usage();

        // Output performance metrics
        $output->writeln([
            "Performance metrics saved to {$csv_path_npf2gb}",
            "Performance metrics saved to {$csv_path_gb2npf}",
            // Amount of blocks converted
            "Total amount of blocks converted: {$total_amount_blocks}",
            "Total amount of NPF posts converted: {$total_amount_npf_posts}",
            // Total time per conversion type
            "Total time for Blocks to NPF conversion: {$total_blocks_to_npf_time} ms",
            "Total time for NPF to Blocks conversion: {$total_npf_to_blocks_time} ms",
        ]);

        // Average time
        $avg_blocks_to_npf_time = $total_blocks_to_npf_time / $total_amount_blocks;
        $avg_npf_to_blocks_time = $total_npf_to_blocks_time / $total_amount_npf_posts;
        $output->writeln([
            "Average time for Blocks to NPF conversion: {$avg_blocks_to_npf_time} ms",
            "Average time for NPF to Blocks conversion: {$avg_npf_to_blocks_time} ms",
        ]);
        // Throughput per second
        $total_npf_to_blocks_time_in_seconds = $total_npf_to_blocks_time / 1000;
        $total_blocks_to_npf_time_in_seconds = $total_blocks_to_npf_time / 1000;
        $blocks_to_npf_throughput = $total_amount_blocks / $total_npf_to_blocks_time_in_seconds;
        $npf_to_blocks_throughput = $total_amount_npf_posts / $total_blocks_to_npf_time_in_seconds;
        $output->writeln([
            "Throughput for Blocks to NPF conversion: {$blocks_to_npf_throughput} blocks/s",
            "Throughput for NPF to Blocks conversion: {$npf_to_blocks_throughput} NPF posts/s",
        ]);
        // Total execution time
        $total_execution_time = $total_blocks_to_npf_time + $total_npf_to_blocks_time;
        $output->writeln([
            "Total execution time: {$total_execution_time} ms",
            "Peak memory usage: {$peak_memory} bytes",
        ]);

        return Command::SUCCESS;
    }
}
