<?php

function grannyYear(): int
{
    return random_int(1930, 1950);
}

function yearAnswer(): string
{
    return 'НЕТ, НИ РАЗУ С ' . grannyYear() . ' ГОДА!';
}

function isFarewell(string $phrase): bool
{
    return mb_strtoupper($phrase) === 'ПОКА!';
}

function run(): void
{
    $farewells = 0;

    fwrite(STDOUT, "ЧЕГО СКАЗАТЬ-ТО ХОТЕЛ, МИЛОК?!\n");

    while (($line = fgets(STDIN)) !== false) {
        $phrase = trim($line);

        if ($phrase === '') {
            continue;
        }

        if (isFarewell($phrase)) {
            $farewells++;
            if ($farewells >= 3) {
                fwrite(STDOUT, "ДО СВИДАНИЯ, МИЛЫЙ!\n");
                return;
            }
            fwrite(STDOUT, yearAnswer() . "\n");
            continue;
        }

        $farewells = 0;

        if (mb_substr($phrase, -1) === '!') {
            fwrite(STDOUT, yearAnswer() . "\n");
        } else {
            fwrite(STDOUT, "АСЬ?! ГОВОРИ ГРОМЧЕ, ВНУЧЕК!\n");
        }
    }
}

run();
