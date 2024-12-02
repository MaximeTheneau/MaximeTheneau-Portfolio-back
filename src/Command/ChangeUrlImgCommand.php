<?php

namespace App\Command;

use App\Entity\Posts;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:change-url-img',
    description: 'Change the URL of images and generate srcset',
)]
class ChangeUrlImgCommand extends Command
{
    private EntityManagerInterface $entityManager;

    private const IMAGE_SIZES = [320, 640, 750, 828, 1080, 1200, 1920, 2048, 3840];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Optional argument')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Optional option');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        $posts = $this->entityManager->getRepository(Posts::class)->findAll();

        foreach ($posts as $post) {
            $slug = $post->getSlug();
            $urlImg = $_ENV['DOMAIN_IMG'] . $slug . '.webp';
            $tempFilePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $slug . '.webp';

            try {
                $imageContent = file_get_contents($urlImg);
                if ($imageContent === false) {
                    throw new \RuntimeException("Failed to download image: $urlImg");
                }

                file_put_contents($tempFilePath, $imageContent);

                if (!file_exists($tempFilePath)) {
                    throw new \RuntimeException("Temporary file not found: $tempFilePath");
                }

                [$width, $height] = getimagesize($tempFilePath);
                if (!$width || !$height) {
                    throw new \RuntimeException("Invalid dimensions for image: $tempFilePath");
                }

                $post->setImgWidth($width);
                $post->setImgHeight($height);
                $post->setImgPost($urlImg);

                $srcset = '';
                foreach (self::IMAGE_SIZES as $size) {
                    if ($size <= $width) {
                        $srcset .= $urlImg . '?width=' . $size . ' ' . $size . 'w, ';
                    }
                }
                $srcset .= $urlImg . ' ' . $width . 'w';
                $post->setSrcset(trim($srcset, ', '));

                $this->entityManager->persist($post);
            } catch (\Exception $e) {
                $io->error("Error processing post ID {$post->getId()}: " . $e->getMessage());
            } finally {
                if (file_exists($tempFilePath)) {
                    unlink($tempFilePath);
                }
            }
        }

        $this->entityManager->flush();

        $io->success('All images processed successfully.');
        return Command::SUCCESS;
    }
}
