<?php declare(strict_types=1);

class Usuario extends Model
{
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findByTelefono(string $telefono): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE telefono = ? LIMIT 1');
        $stmt->execute([$telefono]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function findByTokenVerificacion(string $token): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios WHERE token_verificacion = ? LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function findByTokenRecuperacion(string $token): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios
             WHERE token_recuperacion = ?
               AND token_recuperacion_expira > NOW()
             LIMIT 1'
        );
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO usuarios
                (nombre, fecha_nacimiento, sexo, telefono, email, password, token_verificacion)
             VALUES
                (:nombre, :fecha_nacimiento, :sexo, :telefono, :email, :password, :token_verificacion)'
        );
        $stmt->execute([
            ':nombre'              => $data['nombre'],
            ':fecha_nacimiento'    => $data['fecha_nacimiento'] ?? null,
            ':sexo'                => $data['sexo']             ?? null,
            ':telefono'            => $data['telefono'],
            ':email'               => $data['email'],
            ':password'            => $data['password'],
            ':token_verificacion'  => $data['token_verificacion'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function verificarEmail(string $token): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET email_verificado = 1, token_verificacion = NULL
             WHERE token_verificacion = ?'
        );
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    public function setTokenRecuperacion(int $id, string $token, string $expira): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET token_recuperacion = ?, token_recuperacion_expira = ?
             WHERE id = ?'
        );
        $stmt->execute([$token, $expira, $id]);
        return $stmt->rowCount() > 0;
    }

    public function actualizarDatos(int $id, string $nombre, string $telefono): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios SET nombre = ?, telefono = ? WHERE id = ?'
        );
        $stmt->execute([$nombre, $telefono, $id]);
        return $stmt->rowCount() > 0;
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE usuarios
             SET password = ?,
                 token_recuperacion = NULL,
                 token_recuperacion_expira = NULL
             WHERE id = ?'
        );
        $stmt->execute([$hash, $id]);
        return $stmt->rowCount() > 0;
    }
}
