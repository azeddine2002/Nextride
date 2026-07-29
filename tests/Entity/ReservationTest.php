<?php

namespace App\Tests\Entity;

use App\Entity\Reservation;
use PHPUnit\Framework\TestCase;

class ReservationTest extends TestCase
{
    public function testPeriodeValideQuandFinApresDebut(): void
    {
        $reservation = new Reservation();
        $reservation->setDateDebut(new \DateTimeImmutable('2026-08-01'));
        $reservation->setDateFin(new \DateTimeImmutable('2026-08-05'));

        $this->assertTrue($reservation->estPeriodeValide());
    }

    public function testPeriodeInvalideQuandFinAvantDebut(): void
    {
        $reservation = new Reservation();
        $reservation->setDateDebut(new \DateTimeImmutable('2026-08-05'));
        $reservation->setDateFin(new \DateTimeImmutable('2026-08-01'));

        $this->assertFalse($reservation->estPeriodeValide());
    }

    public function testPeriodeInvalideQuandDatesIdentiques(): void
    {
        $date = new \DateTimeImmutable('2026-08-01');

        $reservation = new Reservation();
        $reservation->setDateDebut($date);
        $reservation->setDateFin($date);

        $this->assertFalse($reservation->estPeriodeValide());
    }

    public function testPeriodeInvalideQuandDatesManquantes(): void
    {
        $reservation = new Reservation();

        $this->assertFalse($reservation->estPeriodeValide());
    }
}
