#!/bin/bash

# Création du dossier des modèles
mkdir -p public/models/heroes

# Liste des modèles à télécharger
declare -A models=(
    ["robot.glb"]="https://raw.githubusercontent.com/KhronosGroup/glTF-Sample-Models/master/2.0/RobotExpressive/glTF-Binary/RobotExpressive.glb"
    ["soldier.glb"]="https://raw.githubusercontent.com/KhronosGroup/glTF-Sample-Models/master/2.0/CesiumMan/glTF-Binary/CesiumMan.glb"
    ["drone.glb"]="https://raw.githubusercontent.com/KhronosGroup/glTF-Sample-Models/master/2.0/DroneRace/glTF-Binary/DroneRace.glb"
    ["fox.glb"]="https://raw.githubusercontent.com/KhronosGroup/glTF-Sample-Models/master/2.0/Fox/glTF-Binary/Fox.glb"
    ["duck.glb"]="https://raw.githubusercontent.com/KhronosGroup/glTF-Sample-Models/master/2.0/Duck/glTF-Binary/Duck.glb"
)

# Téléchargement des modèles
cd public/models/heroes
for model in "${!models[@]}"; do
    echo "Téléchargement de $model..."
    curl -L -o "$model" "${models[$model]}"
done

echo "Tous les modèles ont été téléchargés dans public/models/heroes/"
