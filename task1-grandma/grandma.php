<?php

function granny_year(): int
{
    return random_int(1930, 1950);
}

function yell_year_answer(): string
{
    return 'НЕТ, НИ РАЗУ С ' . granny_year() . ' ГОДА!';
}

function run(): void
{
    $farewellCount = 0;

    fwrite(STDOUT, "ЧЕГО СКАЗАТЬ-ТО ХОТЕЛ, МИЛОК?!\n");

    while (($line = fgets(STDIN)) !== false) {
        $phrase = trim($line);

        if ($phrase === '') {
            continue;
        }

        // «ПОКА!» три раза подряд, только если каждая строка ровно «ПОКА!»
        if ($phrase === 'ПОКА!') {
            $farewellCount++;
            if ($farewellCount >= 3) {
                fwrite(STDOUT, "ДО СВИДАНИЯ, МИЛЫЙ!\n");
                return;
            }

            // бабушка притворяется, что не расслышала
            fwrite(STDOUT, "АСЬ?! ГОВОРИ ГРОМЧЕ, ВНУЧЕК!\n");
            continue;
        }

        // любая другая фраза сбрасывает счётчик прощаний
        $farewellCount = 0;

        $isShout = mb_substr($phrase, -1) === '!';

        if ($isShout) {
            fwrite(STDOUT, yell_year_answer() . "\n");
        } else {
            fwrite(STDOUT, "АСЬ?! ГОВОРИ ГРОМЧЕ, ВНУЧЕК!\n");
        }
    }

    // поток ввода закончился, не дождавшись трёх «ПОКА!»
    fwrite(STDOUT, "АСЬ?! ТЫ КУДА ПРОПАЛ-ТО, ВНУЧЕК?!\n");
}

run();
