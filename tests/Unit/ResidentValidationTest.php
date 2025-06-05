<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\ResidentController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResidentValidationTest extends TestCase
{
    /**
     * Test des méthodes utilitaires de validation des téléphones
     */
    public function test_clean_phone_number()
    {
        $controller = new ResidentController();
        
        // Utiliser la réflexion pour accéder aux méthodes privées
        $reflection = new \ReflectionClass($controller);
        $cleanMethod = $reflection->getMethod('cleanPhoneNumber');
        $cleanMethod->setAccessible(true);
        
        $validMethod = $reflection->getMethod('isValidFrenchPhone');
        $validMethod->setAccessible(true);
        
        // Test de nettoyage des numéros
        $this->assertEquals('0123456789', $cleanMethod->invoke($controller, '01 23 45 67 89'));
        $this->assertEquals('0123456789', $cleanMethod->invoke($controller, '01.23.45.67.89'));
        $this->assertEquals('0123456789', $cleanMethod->invoke($controller, '01-23-45-67-89'));
        $this->assertEquals('0123456789', $cleanMethod->invoke($controller, '+33123456789'));
        
        // Test de validation des numéros français
        $this->assertTrue($validMethod->invoke($controller, '0123456789'));
        $this->assertTrue($validMethod->invoke($controller, '0623456789'));
        $this->assertTrue($validMethod->invoke($controller, '+33123456789'));
        $this->assertTrue($validMethod->invoke($controller, '01 23 45 67 89'));
        
        // Test de numéros invalides
        $this->assertFalse($validMethod->invoke($controller, '123456789')); // Pas de 0 en début
        $this->assertFalse($validMethod->invoke($controller, '0023456789')); // Commence par 00
        $this->assertFalse($validMethod->invoke($controller, '01234567890')); // Trop long
        $this->assertFalse($validMethod->invoke($controller, '012345678')); // Trop court
        $this->assertFalse($validMethod->invoke($controller, '')); // Vide
    }
    
    /**
     * Test de la validation des données de parents
     */
    public function test_validate_parents_data()
    {
        $controller = new ResidentController();
        
        $reflection = new \ReflectionClass($controller);
        $validateMethod = $reflection->getMethod('validateParentsData');
        $validateMethod->setAccessible(true);
        
        // Test avec des données cohérentes
        $validParents = [
            ['nom' => 'Dupont', 'tel' => '0123456789', 'profession' => 'Médecin'],
            ['nom' => 'Martin', 'tel' => '0623456789', 'profession' => 'Avocat']
        ];
        $errors = $validateMethod->invoke($controller, $validParents);
        $this->assertEmpty($errors);
        
        // Test avec nom mais pas de téléphone
        $invalidParents1 = [
            ['nom' => 'Dupont', 'tel' => '', 'profession' => 'Médecin']
        ];
        $errors = $validateMethod->invoke($controller, $invalidParents1);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('parents.0.tel', $errors);
        
        // Test avec téléphone mais pas de nom
        $invalidParents2 = [
            ['nom' => '', 'tel' => '0123456789', 'profession' => '']
        ];
        $errors = $validateMethod->invoke($controller, $invalidParents2);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('parents.0.nom', $errors);
        
        // Test avec téléphone invalide
        $invalidParents3 = [
            ['nom' => 'Dupont', 'tel' => '123456', 'profession' => 'Médecin']
        ];
        $errors = $validateMethod->invoke($controller, $invalidParents3);
        $this->assertNotEmpty($errors);
        $this->assertArrayHasKey('parents.0.tel', $errors);
    }
}
