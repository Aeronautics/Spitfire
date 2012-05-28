SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `politico`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `politico` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `criacao` DATETIME NOT NULL ,
  `nome` VARCHAR(45) NOT NULL ,
  `sala` VARCHAR(45) NULL ,
  `fax` VARCHAR(55) NULL ,
  `ramal` VARCHAR(255) NULL ,
  `promovente_id` INT NULL COMMENT 'Nó no XML: COD_PRVM_APL\nDescrição: Código de promovente do(a) Sr.(a) Vereador(a)\nURL: http://www2.camara.sp.gov.br/Dados_abertos/vereador/Lista_Vereadores.xml' ,
  `gabinete_id` INT NULL COMMENT 'Nó no XML: GV\nDescrição: Número do Gabinete\nURL: http://www2.camara.sp.gov.br/Dados_abertos/vereador/Lista_Vereadores.xml' ,
  `biografia` TEXT NULL ,
  `foto` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `sessaoTipo`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `sessaoTipo` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `nome` VARCHAR(45) NOT NULL ,
  `criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `esfera`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `esfera` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `sigla` VARCHAR(10) NOT NULL ,
  `nome` VARCHAR(255) NOT NULL ,
  `poder` ENUM('Judiciario', 'Legislativo', 'Executivo') NOT NULL ,
  `limite` ENUM('Uniao', 'Estado', 'Município') NOT NULL ,
  `criacao` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB
COMMENT = 'É a esfera política onde';


-- -----------------------------------------------------
-- Table `sessao`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `sessao` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `esfera_id` INT NOT NULL ,
  `sessaoTipo_id` INT NOT NULL ,
  `data` DATE NOT NULL ,
  `descricao` TEXT NOT NULL ,
  `codigo` VARCHAR(45) NOT NULL ,
  `criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_sessao_sessaotipo` (`sessaoTipo_id` ASC) ,
  INDEX `fk_sessao_esfera1` (`esfera_id` ASC) ,
  CONSTRAINT `fk_sessao_sessaotipo`
    FOREIGN KEY (`sessaoTipo_id` )
    REFERENCES `sessaoTipo` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_sessao_esfera1`
    FOREIGN KEY (`esfera_id` )
    REFERENCES `esfera` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `partido`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `partido` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `sigla` VARCHAR(5) NOT NULL ,
  `nome` VARCHAR(255) NULL COMMENT '	' ,
  `criacao` DATETIME NULL ,
  `endereco` TEXT NULL ,
  `telefone` VARCHAR(45) NULL ,
  `cep` CHAR(8) NULL ,
  `fax` VARCHAR(45) NULL ,
  `site` VARCHAR(255) NULL ,
  `email` VARCHAR(255) NULL ,
  `numero` INT(5) NULL ,
  `deferimento` VARCHAR(155) NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `politicoNome`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `politicoNome` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `politico_id` INT NOT NULL ,
  `nome` VARCHAR(255) NOT NULL ,
  `criacao` DATETIME NOT NULL ,
  PRIMARY KEY (`id`, `politico_id`) ,
  CONSTRAINT `fk_nome_parlamentar1`
    FOREIGN KEY (`politico_id` )
    REFERENCES `politico` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mandato`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `mandato` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `criacao` DATETIME NOT NULL ,
  `inicio` DATE NOT NULL ,
  `fim` DATE NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `presenca`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `presenca` (
  `id` INT NOT NULL ,
  `sessao_id` INT NOT NULL ,
  `politico_id` INT NOT NULL ,
  `hora` TIME NOT NULL ,
  `criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_presenca_sessao` (`sessao_id` ASC) ,
  INDEX `fk_presenca_parlamentar` (`politico_id` ASC) ,
  CONSTRAINT `fk_presenca_sessao`
    FOREIGN KEY (`sessao_id` )
    REFERENCES `sessao` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_presenca_parlamentar`
    FOREIGN KEY (`politico_id` )
    REFERENCES `politico` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `votacao`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `votacao` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `sessao_id` INT NOT NULL ,
  `id_interno` TEXT NOT NULL ,
  `matéria` TEXT NOT NULL ,
  `data` DATE NOT NULL ,
  `nome` TEXT NOT NULL ,
  `tipo_votacao` VARCHAR(255) NULL ,
  `resultado` VARCHAR(255) NULL ,
  `ementa` TEXT NULL ,
  `notas_rodape` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_votacao_sessao1` (`sessao_id` ASC) ,
  CONSTRAINT `fk_votacao_sessao1`
    FOREIGN KEY (`sessao_id` )
    REFERENCES `sessao` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `voto`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `voto` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `votacao_id` INT NOT NULL ,
  `politico_id` INT NOT NULL ,
  `voto` ENUM('S', 'N', 'A', 'O') NOT NULL COMMENT 'Sim\nNão\nAbstenção\nObstrução (Não votou)' ,
  `criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_voto_parlamentar` (`politico_id` ASC) ,
  INDEX `fk_voto_votacao1` (`votacao_id` ASC) ,
  CONSTRAINT `fk_voto_parlamentar`
    FOREIGN KEY (`politico_id` )
    REFERENCES `politico` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_voto_votacao1`
    FOREIGN KEY (`votacao_id` )
    REFERENCES `votacao` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `meteriaTipo`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `meteriaTipo` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `criacao` TIMESTAMP NOT NULL ,
  `codigo` VARCHAR(45) NOT NULL ,
  `abreviacao` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `materia`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `materia` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `id_interno` TEXT NOT NULL ,
  `politico_id` INT NOT NULL ,
  `meteriaTipo_id` INT NOT NULL ,
  `criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_materia_parlamentar` (`politico_id` ASC) ,
  INDEX `fk_materia_meteriatipo` (`meteriaTipo_id` ASC) ,
  CONSTRAINT `fk_materia_parlamentar`
    FOREIGN KEY (`politico_id` )
    REFERENCES `politico` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_materia_meteriatipo`
    FOREIGN KEY (`meteriaTipo_id` )
    REFERENCES `meteriaTipo` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `despesatipo`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `despesatipo` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `criacao` TIMESTAMP NOT NULL ,
  `descricao` VARCHAR(45) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `empresa`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `empresa` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `cnpj` INT NOT NULL ,
  `razao_social` VARCHAR(255) NOT NULL ,
  `criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `cnpj_UNIQUE` (`cnpj` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `despesa`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `despesa` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `criacao` TIMESTAMP NOT NULL ,
  `valor` FLOAT NOT NULL ,
  `despesatipo_id` INT NOT NULL ,
  `empresa_id` INT NOT NULL ,
  `parlamentar_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_despesa_despesatipo1` (`despesatipo_id` ASC) ,
  INDEX `fk_despesa_empresa1` (`empresa_id` ASC) ,
  INDEX `fk_despesa_parlamentar1` (`parlamentar_id` ASC) ,
  CONSTRAINT `fk_despesa_despesatipo1`
    FOREIGN KEY (`despesatipo_id` )
    REFERENCES `despesatipo` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_despesa_empresa1`
    FOREIGN KEY (`empresa_id` )
    REFERENCES `empresa` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_despesa_parlamentar1`
    FOREIGN KEY (`parlamentar_id` )
    REFERENCES `politico` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `materiaTag`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `materiaTag` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `nome` VARCHAR(45) NOT NULL ,
  `criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `materia_materiatag`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `materia_materiatag` (
  `materia_id` INT NOT NULL ,
  `materiaTag_id` INT NOT NULL ,
  INDEX `fk_materia_materiatag_materiatag` (`materiaTag_id` ASC) ,
  INDEX `fk_materia_materiatag_materia` (`materia_id` ASC) ,
  PRIMARY KEY (`materia_id`, `materiaTag_id`) ,
  CONSTRAINT `fk_materia_materiatag_materia`
    FOREIGN KEY (`materia_id` )
    REFERENCES `materia` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_materia_materiatag_materiatag`
    FOREIGN KEY (`materiaTag_id` )
    REFERENCES `materiaTag` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `esfera_mandato`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `esfera_mandato` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `esfera_id` INT NOT NULL ,
  `mandato_id` INT NOT NULL ,
  INDEX `fk_esfera_mandato_mandato1` (`mandato_id` ASC) ,
  INDEX `fk_esfera_mandato_esfera1` (`esfera_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_esfera_mandato_esfera1`
    FOREIGN KEY (`esfera_id` )
    REFERENCES `esfera` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_esfera_mandato_mandato1`
    FOREIGN KEY (`mandato_id` )
    REFERENCES `mandato` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mandato_politico`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `mandato_politico` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `mandato_id` INT NOT NULL ,
  `politico_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_mandato_parlamentar_parlamentar1` (`politico_id` ASC) ,
  INDEX `fk_mandato_parlamentar_mandato1` (`mandato_id` ASC) ,
  CONSTRAINT `fk_mandato_parlamentar_mandato1`
    FOREIGN KEY (`mandato_id` )
    REFERENCES `mandato` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_mandato_parlamentar_parlamentar1`
    FOREIGN KEY (`politico_id` )
    REFERENCES `politico` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `partido_presidente`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `partido_presidente` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `partido_id` INT NOT NULL ,
  `politico_id` INT NOT NULL ,
  `desde` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_politico_partido_partido1` (`partido_id` ASC) ,
  INDEX `fk_politico_partido_politico1` (`politico_id` ASC) ,
  CONSTRAINT `fk_politico_partido_politico1`
    FOREIGN KEY (`politico_id` )
    REFERENCES `politico` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_politico_partido_partido1`
    FOREIGN KEY (`partido_id` )
    REFERENCES `partido` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `politico_partido`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `politico_partido` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `politico_id` INT NOT NULL ,
  `partido_id` INT NOT NULL ,
  `desde` DATETIME NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_politico_partido_partido2` (`partido_id` ASC) ,
  INDEX `fk_politico_partido_politico2` (`politico_id` ASC) ,
  CONSTRAINT `fk_politico_partido_politico2`
    FOREIGN KEY (`politico_id` )
    REFERENCES `politico` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_politico_partido_partido2`
    FOREIGN KEY (`partido_id` )
    REFERENCES `partido` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
