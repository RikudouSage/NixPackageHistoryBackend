{ pkgs ? import <nixpkgs> {} }:
pkgs.mkShell {
    nativeBuildInputs = with pkgs.buildPackages;
    let
        php83 = pkgs.php83.buildEnv {
            extraConfig = ''
                memory_limit=8G
            '';
        };
        sls = import (builtins.fetchTarball https://github.com/nixos/nixpkgs/tarball/667993862518f5a890747dfe7aba2c6d0c7787ce) {};
     in
     [
        php83
        php83.packages.composer
        symfony-cli
        git
        nodejs_22
        sls.nodePackages.serverless
    ];
}
