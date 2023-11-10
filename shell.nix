{ pkgs ? import <nixpkgs> {} }:
pkgs.mkShell {
    nativeBuildInputs = with pkgs.buildPackages;
    let
        php82 = pkgs.php82.buildEnv {
            extraConfig = ''
                memory_limit=8G
            '';
        };
     in
     [
        php82
        php82.packages.composer
        symfony-cli
        git
    ];
}
