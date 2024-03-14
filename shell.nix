{ pkgs ? import <nixpkgs> {} }:
pkgs.mkShell {
    nativeBuildInputs = with pkgs.buildPackages;
    let
        php83 = pkgs.php83.buildEnv {
            extraConfig = ''
                memory_limit=8G
            '';
        };
     in
     [
        php83
        php83.packages.composer
        symfony-cli
        git
        nodejs_18
        nodePackages.serverless
    ];
}
